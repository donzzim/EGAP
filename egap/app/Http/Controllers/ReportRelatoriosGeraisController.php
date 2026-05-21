<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ReportRelatoriosGeraisController extends Controller
{
    public function imprimir(Request $request)
    {
        $filtros = $request->all();
        $nomeFormatado = str($filtros['relatorio'] ?? '')->studly();
        $metodo = 'gerar' . $nomeFormatado;

        if (method_exists($this, $metodo)) {
            return $this->$metodo($filtros);
        }

        abort(404, 'Relatório não encontrado: ' . $metodo);
    }

    private function getPeriodo($filtros)
    {
        $inicio = !empty($filtros['data_inicio']) ? Carbon::parse($filtros['data_inicio']) : now();
        $termino = !empty($filtros['data_termino']) ? Carbon::parse($filtros['data_termino']) : now();

        return (object) [
            'objIni'  => $inicio,
            'objFim'  => $termino,
            'ini'     => $inicio->format('Y-m-d 00:00:01'),
            'fim'     => $termino->format('Y-m-d 23:59:59'),
            'iniDate' => $inicio->format('Y-m-d'),
            'fimDate' => $termino->format('Y-m-d'),
            'mesRef'  => $termino->format('Y-m'),
            'mesAnt'  => $inicio->copy()->subMonth()->format('Y-m'),
            'ano'     => $inicio->format('Y'),
        ];
    }

    private function render($viewName, $dados, $filtros, $extra = [])
    {
        $payload = array_merge([
            'dados' => $dados,
            'filtros' => $filtros,
            'data_emissao' => now()->format('d/m/Y')
        ], $extra);

        return view("egap.relatorios.{$viewName}", $payload);
    }

    private function aplicarFiltros($query, $filtros, $colConta = 'pc.id', $colSituacao = 'p.situacao_contabil')
    {
        $query->when($filtros['conta_contabil'] ?? null, fn($q, $v) => $q->where($colConta, $v));
        
        if ($colSituacao) {
            $query->when($filtros['situacao_contabil'] ?? 'Todos', fn($q, $v) => $v !== 'Todos' ? $q->where($colSituacao, $v) : null);
        }
    }

    private function subObrasImoveis($fimStr)
    {
        return DB::connection('egap')->table('imo_obras')
            ->select('id_imovel', DB::raw('SUM(valor) as valor_obra'))
            ->where('data', '<=', $fimStr)
            ->groupBy('id_imovel');
    }

    private function subDepreciacaoImoveis($mesRef)
    {
        return DB::connection('egap')->table('imo_depreciacao')
            ->select('Id_imovel', 'valor', 'depreciacao_mensal', 'depreciacao_acumulada', 'valor_liquido_contabil', 'valor_residual')
            ->whereRaw("DATE_FORMAT(data_calculo, '%Y-%m') = ?", [$mesRef]);
    }

    private function gerarTabela10($filtros)
    {
        $d = $this->getPeriodo($filtros);

        $iniDep = $d->objFim->copy()->startOfMonth()->format('Y-m-d 00:00:00');
        $fimDep = $d->objFim->copy()->endOfMonth()->format('Y-m-d 23:59:59');

        $val = "IF(DatadeIncorporacao < '2015-01-01 00:00:01', ROUND(ValordaReavaliacao,4), ROUND(ValorAquisicao,4))";
        $reavCond = "(DatadaReavaliacao < '{$d->ini}' OR DatadaReavaliacao = '0000-00-00 00:00:00')";

        $subDepre = DB::connection('egap')->table('mat_depreciacao as d')
            ->join('mat_patrimonio as p', 'p.id', '=', 'd.patrimonio')
            ->where('d.item', '>', 1)
            ->whereBetween('d.data_calculo', [$iniDep, $fimDep])
            ->whereRaw("((p.DatadeIncorporacao < ? AND p.SituacaoBem NOT IN (2,3,4,5,6,8,9)) OR (p.DataBaixa > ? AND p.SituacaoBem IN (2,3,4,5,6)))", [$iniDep, $d->fim])
            ->whereRaw("(p.DatadaReavaliacao < ? OR p.DatadaReavaliacao = '0000-00-00 00:00:00')", [$iniDep])
            ->select('p.ContaContabil', 'p.Produto', DB::raw("SUM(d.depreciacao_acumulada) as depreciacao_acumulada"))
            ->groupBy('p.ContaContabil', 'p.Produto');

        $dados = DB::connection('egap')->table('mat_patrimonio as pat')
            ->join('mat_planocontas as pc', 'pc.id', '=', 'pat.ContaContabil')
            ->join('mat_produtos as prod', 'prod.id', '=', 'pat.Produto')
            ->leftJoinSub($subDepre, 'dep', function($join) {
                $join->on('pat.ContaContabil', '=', 'dep.ContaContabil')
                     ->on('pat.Produto', '=', 'dep.Produto');
            })
            ->selectRaw("
                pc.codigo as conta_contabil, prod.CodigodaClasse as cod_nat_despesa, prod.DescricaodaClasse as descricao,
                SUM(CASE WHEN ((DatadeIncorporacao < '{$d->ini}' AND SituacaoBem NOT IN (2,3,4,5,6,8,9)) OR (DataBaixa >= '{$d->fim}' AND SituacaoBem IN (2,3,4,5,6)) OR 
                (DataBaixa BETWEEN '{$d->ini}' AND '{$d->fim}' AND SituacaoBem IN (2,3,4,5,6))) AND $reavCond THEN $val ELSE 0 END) 
                - SUM(CASE WHEN DataBaixa >= '{$d->fim}' AND DatadeIncorporacao BETWEEN '{$d->ini}' AND '{$d->fim}' THEN $val ELSE 0 END) as saldo_anterior,
                SUM(CASE WHEN DatadeIncorporacao BETWEEN '{$d->ini}' AND '{$d->fim}' AND SituacaoBem NOT IN (8,9) THEN $val ELSE 0 END) 
                + SUM(CASE WHEN DatadaReavaliacao BETWEEN '{$d->ini}' AND '{$d->fim}' AND SituacaoBem NOT IN (8,9) THEN ValordaReavaliacao ELSE 0 END) as entradas,
                SUM(CASE WHEN DataBaixa BETWEEN '{$d->ini}' AND '{$d->fim}' AND SituacaoBem IN (2,3,4,5,6) THEN $val ELSE 0 END) as saidas,
                IFNULL(dep.depreciacao_acumulada, 0) as depreciacao
            ")
            ->tap(fn($q) => $this->aplicarFiltros($q, $filtros, 'pc.id', 'pat.situacao_contabil'))
            ->when($filtros['unidade_gestora'] ?? 'Todos', fn($q, $v) => $v !== 'Todos' ? $q->where('pat.unidade_gestora', $v) : null)
            ->groupBy('pc.codigo', 'prod.CodigodaClasse', 'prod.DescricaodaClasse', 'pat.ContaContabil', 'pat.Produto', 'dep.depreciacao_acumulada')
            ->orderBy('pc.codigo')->orderBy('prod.CodigodaClasse')
            ->get()
            ->map(fn($i) => (object)[... (array)$i, 
                'saldo_bruto' => ($i->saldo_anterior + $i->entradas) - $i->saidas,
                'saldo_atual' => ($i->saldo_anterior + $i->entradas - $i->saidas) - $i->depreciacao
            ]);

        return $this->render('tce-tabela-10', $dados, $filtros);
    }
    
    private function gerarTabela11($filtros)
    {
        $d = $this->getPeriodo($filtros);
        $val = "IF(DatadeIncorporacao < '2015-01-01 00:00:01', ROUND(ValordaReavaliacao,4), ROUND(ValorAquisicao,4))";

        $dados = DB::connection('egap')->table('mat_patrimonio as pat')
            ->join('mat_planocontas as pc', 'pc.id', 'pat.ContaContabil')
            ->join('mat_produtos as prod', 'prod.id', 'pat.Produto')
            ->selectRaw("
                pc.codigo as conta_contabil, prod.CodigodaClasse as cod_nat_despesa, prod.DescricaodaClasse as descricao,
                SUM(CASE WHEN DatadeIncorporacao BETWEEN '{$d->ini}' AND '{$d->fim}' AND SituacaoBem NOT IN (8,9) AND FormaAquisicao = 'Compra' THEN $val ELSE 0 END) as ent_incorporadas,
                SUM(CASE WHEN DatadeIncorporacao BETWEEN '{$d->ini}' AND '{$d->fim}' AND SituacaoBem NOT IN (8,9) AND FormaAquisicao = 'Doação' THEN $val ELSE 0 END) as ent_doacao,
                SUM(CASE WHEN DatadaReavaliacao BETWEEN '{$d->ini}' AND '{$d->fim}' AND SituacaoBem NOT IN (8,9) THEN ValordaReavaliacao ELSE 0 END) as ent_outras,
                0 as sai_alienacao,
                SUM(CASE WHEN DataBaixa BETWEEN '{$d->ini}' AND '{$d->fim}' AND SituacaoBem = 3 THEN $val ELSE 0 END) as sai_doacao,
                SUM(CASE WHEN DataBaixa BETWEEN '{$d->ini}' AND '{$d->fim}' AND SituacaoBem = 5 THEN $val ELSE 0 END) as sai_perda,
                SUM(CASE WHEN DataBaixa BETWEEN '{$d->ini}' AND '{$d->fim}' AND SituacaoBem IN (2,4,6) THEN $val ELSE 0 END) as sai_outras
            ")
            ->tap(fn($q) => $this->aplicarFiltros($q, $filtros, 'pc.id', 'pat.situacao_contabil'))
            ->groupBy('pc.codigo', 'prod.CodigodaClasse', 'prod.DescricaodaClasse', 'pat.ContaContabil', 'pat.Produto')
            ->orderBy('pc.codigo')->orderBy('prod.CodigodaClasse')
            ->get()
            ->map(fn($i) => (object)[... (array)$i, 
                'total_entradas' => $i->ent_incorporadas + $i->ent_doacao + $i->ent_outras,
                'total_saidas' => $i->sai_alienacao + $i->sai_doacao + $i->sai_perda + $i->sai_outras
            ]);

        return $this->render('tce-tabela-11', $dados, $filtros);
    }

    private function gerarTabela12($filtros)
    {
        $d = $this->getPeriodo($filtros);

        $sa = DB::connection('egap')->table('imo_imovel as imo')
            ->leftJoinSub(DB::connection('egap')->table('imo_reavaliacao')->select('Id_imovel', DB::raw('MAX(data_reavaliacao) as data'))->where('data_reavaliacao', '<', $d->ini)->groupBy('Id_imovel'), 'u_rea', 'imo.id', '=', 'u_rea.Id_imovel')
            ->leftJoin('imo_reavaliacao as rea', fn($join) => $join->on('u_rea.Id_imovel', '=', 'rea.Id_imovel')->on('u_rea.data', '=', 'rea.data_reavaliacao'))
            ->leftJoinSub(DB::connection('egap')->table('imo_obras as o')->leftJoinSub(DB::connection('egap')->table('imo_reavaliacao')->select('Id_imovel', DB::raw('MAX(data_reavaliacao) as d_rea'))->where('data_reavaliacao', '<', $d->ini)->groupBy('Id_imovel'), 'r', 'r.Id_imovel', '=', 'o.Id_imovel')->select('o.id_imovel', DB::raw('SUM(o.valor) as valor'))->whereRaw("o.data BETWEEN IFNULL(r.d_rea, '1900-01-01') AND ?", [$d->ini])->groupBy('o.id_imovel'), 'obra', 'imo.id', '=', 'obra.id_imovel')
            ->select('imo.Id_planocontas as conta', DB::raw('SUM(IFNULL(rea.valor_reavaliacao, imo.valor_historico_1a_avaliacao) + IFNULL(obra.valor, 0)) as total'))
            ->where('imo.Id_situacao', 1)->where('imo.data_aquisicao', '<', $d->ini)
            ->whereRaw("(imo.data_situacao = '0000-00-00 00:00:00' OR imo.data_situacao <= ?)", [$d->fim])
            ->whereRaw("(imo.data_baixa >= ? OR imo.data_baixa = '0000-00-00 00:00:00' OR imo.data_baixa IS NULL)", [$d->fim])
            ->groupBy('imo.Id_planocontas');

        $ent = DB::connection('egap')->table('imo_imovel as imo')
            ->leftJoinSub(DB::connection('egap')->table('imo_reavaliacao')->select('Id_imovel', DB::raw('MAX(data_reavaliacao) as data'))->whereBetween('data_reavaliacao', [$d->ini, $d->fim])->groupBy('Id_imovel'), 'u_rea', 'imo.id', '=', 'u_rea.Id_imovel')
            ->leftJoin('imo_reavaliacao as rea', fn($join) => $join->on('u_rea.Id_imovel', '=', 'rea.Id_imovel')->on('u_rea.data', '=', 'rea.data_reavaliacao'))
            ->leftJoinSub(DB::connection('egap')->table('imo_obras')->select('Id_imovel', DB::raw('SUM(valor) as valor'))->whereBetween('data', [$d->ini, $d->fim])->groupBy('Id_imovel'), 'oe', 'imo.id', '=', 'oe.Id_imovel')
            ->select('imo.Id_planocontas as conta', DB::raw('SUM(IF(rea.ajuste_contabil >= 0, rea.ajuste_contabil, 0) + IFNULL(oe.valor, 0)) as entradas'), DB::raw('SUM(IF(rea.ajuste_contabil < 0, ABS(rea.ajuste_contabil), 0)) as ajustecontabil_saida'))
            ->where('imo.Id_situacao', 1)->where('imo.data_aquisicao', '<', $d->ini)
            ->whereRaw("(imo.data_situacao = '0000-00-00 00:00:00' OR imo.data_situacao <= ?)", [$d->fim])
            ->whereRaw("(imo.data_baixa >= ? OR imo.data_baixa = '0000-00-00 00:00:00' OR imo.data_baixa IS NULL)", [$d->fim])
            ->groupBy('imo.Id_planocontas');

        $sai = DB::connection('egap')->table('imo_imovel as imo')
            ->leftJoinSub(DB::connection('egap')->table('imo_reavaliacao')->select('Id_imovel', DB::raw('MAX(data_reavaliacao) as data'))->where('data_reavaliacao', '<', $d->ini)->groupBy('Id_imovel'), 'u_rea', 'imo.id', '=', 'u_rea.Id_imovel')
            ->leftJoin('imo_reavaliacao as rea', fn($join) => $join->on('u_rea.Id_imovel', '=', 'rea.Id_imovel')->on('u_rea.data', '=', 'rea.data_reavaliacao'))
            ->leftJoinSub(DB::connection('egap')->table('imo_obras')->select('Id_imovel', DB::raw('MAX(data) as data'))->groupBy('Id_imovel'), 'u_obra', 'imo.id', '=', 'u_obra.Id_imovel')
            ->leftJoinSub(DB::connection('egap')->table('imo_obras as o')->leftJoin('imo_reavaliacao as r', 'r.Id_imovel', '=', 'o.Id_imovel')->select('o.valor', 'o.data', 'o.Id_imovel')->whereRaw('(r.data_reavaliacao < o.data OR r.data_reavaliacao IS NULL)'), 'obra', fn($join) => $join->on('u_obra.data', '=', 'obra.data')->on('u_obra.Id_imovel', '=', 'obra.Id_imovel'))
            ->select('imo.Id_planocontas as conta', DB::raw('SUM(IFNULL(rea.valor_reavaliacao, imo.valor_historico_1a_avaliacao) + IFNULL(obra.valor, 0)) as saidas'))
            ->whereBetween('imo.data_baixa', [$d->ini, $d->fim])->groupBy('imo.Id_planocontas');

        $iniDep = $d->objFim->copy()->startOfMonth()->format('Y-m-d 00:00:00');
        $fimDep = $d->objFim->copy()->endOfMonth()->format('Y-m-d 23:59:59');

        $dep = DB::connection('egap')->table('imo_depreciacao as d')
            ->join('imo_imovel as imo', 'imo.id', '=', 'd.Id_imovel')
            ->whereBetween('d.data_calculo', [$iniDep, $fimDep])
            ->where('imo.id_situacao', 1)
            ->select('imo.Id_planocontas as conta', DB::raw('IFNULL(SUM(d.depreciacao_acumulada), 0) as valor'))
            ->groupBy('imo.Id_planocontas');

        $dados = DB::connection('egap')->table('mat_planocontas as pc')
            ->join('imo_imovel as imo', 'pc.id', '=', 'imo.id_planocontas')
            ->leftJoinSub($sa, 'sa', 'pc.id', '=', 'sa.conta')
            ->leftJoinSub($ent, 'ent', 'pc.id', '=', 'ent.conta')
            ->leftJoinSub($sai, 'sai', 'pc.id', '=', 'sai.conta')
            ->leftJoinSub($dep, 'dep', 'pc.id', '=', 'dep.conta')
            ->where('imo.id_situacao', 1)
            ->tap(fn($q) => $this->aplicarFiltros($q, $filtros, 'pc.id', null))
            ->select('pc.codigo as conta_contabil', 'pc.titulo as descricao', DB::raw('IFNULL(sa.total, 0) as saldo_anterior'), DB::raw('IFNULL(ent.entradas, 0) as entradas'), DB::raw('(IFNULL(ent.ajustecontabil_saida, 0) + IFNULL(sai.saidas, 0)) as saidas'), DB::raw('IFNULL(dep.valor, 0) as depreciacao_acumulada'))
            ->distinct()->orderBy('pc.codigo')->get()
            ->map(function($i) {
                $i->saldo_bruto = ($i->saldo_anterior + $i->entradas) - $i->saidas;
                $i->saldo_atual = $i->saldo_bruto - $i->depreciacao_acumulada;
                return $i;
            });

        return $this->render('tce-tabela-12', $dados, $filtros);
    }
    
    private function gerarTabela13($filtros)
    {
        $d = $this->getPeriodo($filtros);
        $val = "IF(imo.data_reavaliacao IS NOT NULL, imo.valor_reavaliado, imo.valor_historico_1a_avaliacao)";

        $agr = DB::connection('egap')->table('imo_imovel as imo')
            ->select('imo.id_planocontas as conta')
            ->selectRaw("SUM(CASE WHEN imo.id_entradasaida = 1 AND imo.data_situacao BETWEEN ? AND ? THEN $val ELSE 0 END) as e_com", [$d->ini, $d->fim])
            ->selectRaw("SUM(CASE WHEN imo.id_entradasaida = 2 AND imo.data_situacao BETWEEN ? AND ? THEN $val ELSE 0 END) as e_doa", [$d->ini, $d->fim])
            ->selectRaw("SUM(CASE WHEN imo.id_entradasaida = 3 AND imo.data_situacao BETWEEN ? AND ? THEN $val ELSE 0 END) as e_con", [$d->ini, $d->fim])
            ->selectRaw("SUM(CASE WHEN imo.id_entradasaida = 4 AND imo.data_situacao BETWEEN ? AND ? THEN $val ELSE 0 END) as e_des", [$d->ini, $d->fim])
            ->selectRaw("SUM(CASE WHEN imo.id_entradasaida = 6 AND imo.data_baixa BETWEEN ? AND ? THEN $val ELSE 0 END) as s_ali", [$d->ini, $d->fim])
            ->selectRaw("SUM(CASE WHEN imo.id_entradasaida = 7 AND imo.data_baixa BETWEEN ? AND ? THEN $val ELSE 0 END) as s_doa", [$d->ini, $d->fim])
            ->selectRaw("SUM(CASE WHEN imo.id_entradasaida = 8 AND imo.data_baixa BETWEEN ? AND ? THEN $val ELSE 0 END) as s_per", [$d->ini, $d->fim])
            ->selectRaw("SUM(CASE WHEN imo.id_entradasaida = 9 AND imo.data_baixa BETWEEN ? AND ? THEN $val ELSE 0 END) as s_out_base", [$d->ini, $d->fim])
            ->groupBy('imo.id_planocontas');

        $obs = DB::connection('egap')->table('imo_imovel as imo')
            ->join('imo_obras as o', 'imo.id', '=', 'o.id_imovel')
            ->select('imo.id_planocontas as conta', DB::raw('SUM(o.valor) as valor'))
            ->whereBetween('o.data', [$d->ini, $d->fim])
            ->where('imo.id_situacao', 1)
            ->groupBy('imo.id_planocontas');

        $eo = DB::connection('egap')->table('imo_imovel as imo')
            ->leftJoinSub(DB::connection('egap')->table('imo_reavaliacao')->select('Id_imovel', DB::raw('MAX(data_reavaliacao) as data'))->whereBetween('data_reavaliacao', [$d->ini, $d->fim])->groupBy('Id_imovel'), 'u_rea', 'imo.id', '=', 'u_rea.Id_imovel')
            ->leftJoin('imo_reavaliacao as rea', fn($join) => $join->on('u_rea.Id_imovel', '=', 'rea.Id_imovel')->on('u_rea.data', '=', 'rea.data_reavaliacao'))
            ->leftJoinSub($this->subObrasImoveis($d->fim), 'oe', 'imo.id', '=', 'oe.Id_imovel')
            ->select('imo.Id_planocontas as conta', DB::raw('SUM(IF(rea.ajuste_contabil >= 0, rea.ajuste_contabil, 0) + IFNULL(oe.valor_obra, 0)) as entradas'), DB::raw('SUM(IF(rea.ajuste_contabil < 0, ABS(rea.ajuste_contabil), 0)) as ajustecontabil_saida'))
            ->where('imo.Id_situacao', 1)->where('imo.data_aquisicao', '<', $d->ini)
            ->whereRaw("(imo.data_situacao = '0000-00-00 00:00:00' OR imo.data_situacao <= ?)", [$d->fim])
            ->whereRaw("(imo.data_baixa >= ? OR imo.data_baixa = '0000-00-00 00:00:00' OR imo.data_baixa IS NULL)", [$d->fim])
            ->groupBy('imo.Id_planocontas');

        $dados = DB::connection('egap')->table('mat_planocontas as pc')
            ->join('imo_imovel as imo', 'pc.id', '=', 'imo.id_planocontas')
            ->leftJoinSub($agr, 'agr', 'pc.id', '=', 'agr.conta')
            ->leftJoinSub($obs, 'obs', 'pc.id', '=', 'obs.conta')
            ->leftJoinSub($eo, 'eo', 'pc.id', '=', 'eo.conta')
            ->where('imo.id_situacao', 1)
            ->tap(fn($q) => $this->aplicarFiltros($q, $filtros, 'pc.id', null))
            ->select('pc.codigo as conta_contabil', 'pc.titulo as descricao', DB::raw('IFNULL(agr.e_com, 0) as ent_compras'), DB::raw('IFNULL(agr.e_doa, 0) as ent_doacao'), DB::raw('(IFNULL(agr.e_con, 0) + IFNULL(obs.valor, 0)) as ent_construcao'), DB::raw('IFNULL(agr.e_des, 0) as ent_desapropriacao'), DB::raw('IFNULL(eo.entradas, 0) as ent_outras'), DB::raw('IFNULL(agr.s_ali, 0) as sai_alienacao'), DB::raw('IFNULL(agr.s_doa, 0) as sai_doacao'), DB::raw('IFNULL(agr.s_per, 0) as sai_perdas'), DB::raw('(IFNULL(agr.s_out_base, 0) + IFNULL(eo.ajustecontabil_saida, 0)) as sai_outras'))
            ->distinct()->orderBy('pc.codigo')->get()
            ->map(function($i) {
                $i->total_entradas = $i->ent_compras + $i->ent_doacao + $i->ent_construcao + $i->ent_desapropriacao + $i->ent_outras;
                $i->total_saidas = $i->sai_alienacao + $i->sai_doacao + $i->sai_perdas + $i->sai_outras;
                return $i;
            });

        return $this->render('tce-tabela-13', $dados, $filtros);
    }

    private function gerarTabela14($filtros)
    {
        $d = $this->getPeriodo($filtros);

        $latestIds = DB::connection('egap')
            ->table('alm_estoque as e2')
            ->select('e2.material', DB::raw('MAX(e2.id) as max_id'))
            ->where('e2.date_time', '<', $d->iniDate)
            ->groupBy('e2.material');

        $query = DB::connection('egap')
            ->table('mat_descricaoresumida as dr')
            ->join('mat_planocontas as pc', 'dr.ContaContabil', '=', 'pc.id')
            ->join('mat_produtos as pr', 'dr.id_produto', '=', 'pr.id')
            // Saldo Anterior
            ->join('mat_descricaodetalhada as dd_sa', 'dr.id', '=', 'dd_sa.descricao_resumida')
            ->leftJoinSub($latestIds, 'latest', fn($j) =>
                $j->on('dd_sa.id', '=', 'latest.material')
            )
            ->leftJoin('alm_estoque as est_sa', 'est_sa.id', '=', 'latest.max_id')
            // Movimentações
            ->leftJoin('mat_descricaodetalhada as dd_mov', 'dr.id', '=', 'dd_mov.descricao_resumida')
            ->leftJoin('alm_estoque as est_mov', function ($j) use ($d) {
                $j->on('dd_mov.id', '=', 'est_mov.material')
                ->whereBetween('est_mov.date_time', [$d->ini, $d->fim])
                ->whereIn('est_mov.tipo_movimentacao', [1, 2]);
            })
            ->where('dr.id_tipo_material', '<>', 'P')
            ->select(
                'pc.id as id_conta',
                'pr.id as id_produto',
                'pc.codigo as conta_contabil',
                'pr.CodigodaClasse as cod_nat_despesa',
                'pr.DescricaodaClasse as descricao',
                DB::raw('COALESCE(SUM(est_sa.valor_total_estoque), 0) as saldo_anterior'),
                DB::raw('COALESCE(SUM(CASE WHEN est_mov.tipo_movimentacao = 1 THEN est_mov.valor_total ELSE 0 END), 0) as entradas'),
                DB::raw('COALESCE(SUM(CASE WHEN est_mov.tipo_movimentacao = 2 THEN est_mov.valor_total ELSE 0 END), 0) as saidas')
            )
            ->groupBy(
                'pc.id',
                'pr.id',
                'pc.codigo',
                'pr.CodigodaClasse',
                'pr.DescricaodaClasse'
            );

        $this->aplicarFiltros($query, $filtros, 'pc.id', null);

        $dados = $query
            ->orderBy('pc.codigo')
            ->orderBy('pr.CodigodaClasse')
            ->get()
            ->map(function ($item) {
                $item->saldo_atual = $item->saldo_anterior + $item->entradas - $item->saidas;
                return $item;
            });

        return $this->render('tce-tabela-14', $dados, $filtros);
    }

    private function gerarTabela15($filtros)
    {
        $d = $this->getPeriodo($filtros);

        $base = DB::connection('egap')->table('mat_descricaoresumida as dr')
            ->join('mat_planocontas as pc', 'dr.ContaContabil', '=', 'pc.id')
            ->join('mat_produtos as pr', 'dr.id_produto', '=', 'pr.id')
            ->select('pc.id as id_conta', 'pr.id as id_produto', 'pc.codigo as ContaContabil', 'pr.CodigodaClasse as Produto', 'pr.DescricaodaClasse as Descricao')
            ->where('dr.id_tipo_material', '<>', 'P')
            ->distinct();

        $mov = DB::connection('egap')->table('alm_estoque as est')
            ->join('mat_descricaodetalhada as dd', 'est.material', '=', 'dd.id')
            ->join('mat_descricaoresumida as dr', 'dd.descricao_resumida', '=', 'dr.id')
            ->leftJoin('alm_notafiscal as nf', 'nf.id', '=', 'est.nota_fiscal')
            ->select('dr.ContaContabil as id_conta', 'dr.id_produto')
            ->selectRaw("SUM(CASE WHEN est.tipo_movimentacao = 1 AND nf.tipo_documento = 1 THEN est.valor_total ELSE 0 END) as e_compras")
            ->selectRaw("SUM(CASE WHEN est.tipo_movimentacao = 1 AND nf.tipo_documento = 3 THEN est.valor_total ELSE 0 END) as e_outras")
            ->selectRaw("SUM(CASE WHEN est.tipo_movimentacao = 2 THEN est.valor_total ELSE 0 END) as s_consumo")
            ->whereBetween('est.date_time', [$d->ini, $d->fim])
            ->whereIn('est.tipo_movimentacao', [1, 2])
            ->groupBy('dr.ContaContabil', 'dr.id_produto');

        $dados = DB::connection('egap')->query()
            ->fromSub($base, 'conta')
            ->leftJoinSub($mov, 'mov', fn($j) => $j->on('conta.id_conta', '=', 'mov.id_conta')->on('conta.id_produto', '=', 'mov.id_produto'))
            ->tap(fn($q) => $this->aplicarFiltros($q, $filtros, 'conta.id_conta', null))
            ->select('conta.ContaContabil as conta_contabil', 'conta.Produto as cod_nat_despesa', 'conta.Descricao as descricao', DB::raw('IFNULL(mov.e_compras, 0) as ent_compras'), DB::raw('0 as ent_doacao'), DB::raw('IFNULL(mov.e_outras, 0) as ent_outras'), DB::raw('IFNULL(mov.s_consumo, 0) as sai_consumo'), DB::raw('0 as sai_doacao'), DB::raw('0 as sai_perdas'), DB::raw('0 as sai_outras'))
            ->orderBy('conta.ContaContabil')->orderBy('conta.Produto')->get()
            ->map(fn($i) => (object)[... (array)$i, 'total_entradas' => $i->ent_compras + $i->ent_doacao + $i->ent_outras, 'total_saidas' => $i->sai_consumo + $i->sai_doacao + $i->sai_perdas + $i->sai_outras]);

        return $this->render('tce-tabela-15', $dados, $filtros);
    }

    private function gerarTabela16($filtros)
    {
        $d = $this->getPeriodo($filtros);

        $base = DB::connection('egap')->table('alm_estoque as est')
            ->join('mat_descricaodetalhada as dd', 'est.material', '=', 'dd.id')
            ->join('mat_descricaoresumida as dr', 'dd.descricao_resumida', '=', 'dr.id')
            ->join('mat_planocontas as pc', 'dr.ContaContabil', '=', 'pc.id')
            ->join('mat_produtos as pr', 'dr.id_produto', '=', 'pr.id')
            ->select('pc.id as id_conta', 'pr.id as id_produto', 'pc.codigo as ContaContabil', 'pr.CodigodaClasse as Produto', 'pr.DescricaodaClasse as Descricao')
            ->where('dr.id_tipo_material', '=', 'P')->distinct();

        $sa = DB::connection('egap')->table('alm_estoque as est')
            ->join('mat_descricaodetalhada as dd', 'est.material', '=', 'dd.id')
            ->join('mat_descricaoresumida as dr', 'dd.descricao_resumida', '=', 'dr.id')
            ->joinSub(DB::connection('egap')->table('alm_estoque')->select(DB::raw('MAX(id) as id'), 'material')->where('date_time', '<', $d->iniDate)->groupBy('material'), 'ult', 'est.id', '=', 'ult.id')
            ->select('dr.ContaContabil as id_conta', 'dr.id_produto', DB::raw('SUM(est.valor_total_estoque) as SaldoAnterior'))->groupBy('dr.ContaContabil', 'dr.id_produto');

        $mov = DB::connection('egap')->table('alm_estoque as est')
            ->join('mat_descricaodetalhada as dd', 'est.material', '=', 'dd.id')
            ->join('mat_descricaoresumida as dr', 'dd.descricao_resumida', '=', 'dr.id')
            ->select('dr.ContaContabil as id_conta', 'dr.id_produto')
            ->selectRaw("SUM(CASE WHEN est.tipo_movimentacao = 1 THEN est.valor_total ELSE 0 END) as ValorEntrada")
            ->selectRaw("SUM(CASE WHEN est.tipo_movimentacao = 2 THEN est.valor_total ELSE 0 END) as ValorSaida")
            ->whereBetween('est.date_time', [$d->ini, $d->fim])->whereIn('est.tipo_movimentacao', [1, 2])->groupBy('dr.ContaContabil', 'dr.id_produto');

        $dados = DB::connection('egap')->query()->fromSub($base, 'conta')
            ->leftJoinSub($sa, 'sa', fn($j) => $j->on('conta.id_conta', '=', 'sa.id_conta')->on('conta.id_produto', '=', 'sa.id_produto'))
            ->leftJoinSub($mov, 'mov', fn($j) => $j->on('conta.id_conta', '=', 'mov.id_conta')->on('conta.id_produto', '=', 'mov.id_produto'))
            ->tap(fn($q) => $this->aplicarFiltros($q, $filtros, 'conta.id_conta', null))
            ->select('conta.ContaContabil as conta_contabil', 'conta.Produto as cod_nat_despesa', 'conta.Descricao as descricao', DB::raw('IFNULL(sa.SaldoAnterior, 0) as saldo_anterior'), DB::raw('IFNULL(mov.ValorEntrada, 0) as entradas'), DB::raw('IFNULL(mov.ValorSaida, 0) as saidas'))
            ->orderBy('conta.ContaContabil')->orderBy('conta.Produto')->get()
            ->map(fn($i) => (object)[... (array)$i, 'saldo_atual' => $i->saldo_anterior + $i->entradas - $i->saidas]);

        return $this->render('tce-tabela-16', $dados, $filtros);
    }

    private function gerarTabela17($filtros)
    {
        $d = $this->getPeriodo($filtros);

        $base = DB::connection('egap')->table('alm_estoque as est')
            ->join('mat_descricaodetalhada as dd', 'est.material', '=', 'dd.id')
            ->join('mat_descricaoresumida as dr', 'dd.descricao_resumida', '=', 'dr.id')
            ->join('mat_planocontas as pc', 'dr.ContaContabil', '=', 'pc.id')
            ->join('mat_produtos as pr', 'dr.id_produto', '=', 'pr.id')
            ->select('pc.id as id_conta', 'pr.id as id_produto', 'pc.codigo as ContaContabil', 'pr.CodigodaClasse as Produto', 'pr.DescricaodaClasse as Descricao')
            ->where('dr.id_tipo_material', '=', 'P')->distinct();

        $mov = DB::connection('egap')->table('alm_estoque as est')
            ->join('mat_descricaodetalhada as dd', 'est.material', '=', 'dd.id')
            ->join('mat_descricaoresumida as dr', 'dd.descricao_resumida', '=', 'dr.id')
            ->leftJoin('alm_notafiscal as nf', 'nf.id', '=', 'est.nota_fiscal')
            ->select('dr.ContaContabil as id_conta', 'dr.id_produto')
            ->selectRaw("SUM(CASE WHEN est.tipo_movimentacao = 1 AND nf.tipo_documento = 1 THEN est.valor_total ELSE 0 END) as e_compras")
            ->selectRaw("SUM(CASE WHEN est.tipo_movimentacao = 1 AND nf.tipo_documento = 2 THEN est.valor_total ELSE 0 END) as e_doacao")
            ->selectRaw("SUM(CASE WHEN est.tipo_movimentacao = 2 AND nf.tipo_documento = 1 THEN est.valor_total ELSE 0 END) as s_consumo")
            ->selectRaw("SUM(CASE WHEN est.tipo_movimentacao = 2 AND nf.tipo_documento = 2 THEN est.valor_total ELSE 0 END) as s_doacao")
            ->whereBetween('est.date_time', [$d->ini, $d->fim])->whereIn('est.tipo_movimentacao', [1, 2])->groupBy('dr.ContaContabil', 'dr.id_produto');

        $dados = DB::connection('egap')->query()->fromSub($base, 'conta')
            ->leftJoinSub($mov, 'mov', fn($j) => $j->on('conta.id_conta', '=', 'mov.id_conta')->on('conta.id_produto', '=', 'mov.id_produto'))
            ->tap(fn($q) => $this->aplicarFiltros($q, $filtros, 'conta.id_conta', null))
            ->select('conta.ContaContabil as conta_contabil', 'conta.Produto as cod_nat_despesa', 'conta.Descricao as descricao', DB::raw('IFNULL(mov.e_compras, 0) as ent_compras'), DB::raw('IFNULL(mov.e_doacao, 0) as ent_doacao'), DB::raw('0 as ent_outras'), DB::raw('IFNULL(mov.s_consumo, 0) as sai_consumo'), DB::raw('IFNULL(mov.s_doacao, 0) as sai_doacao'), DB::raw('0 as sai_perdas'), DB::raw('0 as sai_outras'))
            ->orderBy('conta.ContaContabil')->orderBy('conta.Produto')->get()
            ->map(fn($i) => (object)[... (array)$i, 'total_entradas' => $i->ent_compras, 'total_saidas' => $i->sai_consumo]);

        return $this->render('tce-tabela-17', $dados, $filtros);
    }

    private function gerarBensIncorporados($filtros)
    {
        $d = $this->getPeriodo($filtros);
        $val = "IF(p.DatadeIncorporacao < '2015-01-01 00:00:01', ROUND(p.ValordaReavaliacao,4), ROUND(p.ValorAquisicao,4))";

        $dados = DB::connection('egap')->table('mat_patrimonio as p')
            ->leftJoin('mat_descricaoresumida as dr', 'p.DescricaoResumidadoBem', '=', 'dr.id')
            ->leftJoin('mat_planocontas as pc', 'p.ContaContabil', '=', 'pc.id')
            ->join('mat_situacao as s', 'p.SituacaoBem', '=', 's.id')
            ->leftJoin('mat_setores as set', 'p.Setor', '=', 'set.id')
            ->leftJoin('mat_marca as m', 'p.Marca', '=', 'm.id')
            ->leftJoin('mat_modelo as mod', 'p.Modelo', '=', 'mod.id')
            ->select('pc.codigo as conta_contabil', 'p.NumPatrimonio as patrimonio', 'dr.Descricao as descricao', 'm.Descricao as marca', 'mod.descricao as modelo', 'p.DatadeIncorporacao as data_incorporacao', DB::raw("$val as valor"), 'p.FormaAquisicao as forma_aquisicao', 's.descricao as situacao', 'set.setor')
            ->whereBetween('p.DatadeIncorporacao', [$d->ini, $d->fim])->whereNotIn('p.SituacaoBem', [8, 9])
            ->tap(fn($q) => $this->aplicarFiltros($q, $filtros, 'p.ContaContabil', 'p.situacao_contabil'))
            ->when($filtros['unidade_gestora'] ?? 'Todos', fn($q, $v) => $v !== 'Todos' ? $q->where('p.unidade_gestora', $v) : null)
            ->orderBy('pc.codigo')->orderBy('p.NumPatrimonio')->get();

        return $this->render('bens-incorporados', null, $filtros, ['dadosAgrupados' => $dados->groupBy('conta_contabil')]);
    }

    private function gerarBensBaixados($filtros)
    {
        $d = $this->getPeriodo($filtros);

        $subDepAcum = DB::connection('egap')->table('mat_depreciacao as d')->whereColumn('d.patrimonio', 'p.id')->whereRaw("DATE_FORMAT(d.data_calculo,'%m%Y') = DATE_FORMAT(p.DataBaixa + INTERVAL -1 MONTH,'%m%Y')")->select('d.depreciacao_acumulada')->limit(1);
        $subValorLiq = DB::connection('egap')->table('mat_depreciacao as d')->whereColumn('d.patrimonio', 'p.id')->whereRaw("DATE_FORMAT(d.data_calculo,'%m%Y') = DATE_FORMAT(p.DataBaixa + INTERVAL -1 MONTH,'%m%Y')")->select('d.valor_liquido_contabil')->limit(1);
        $valBruto = "IF(p.DatadeIncorporacao < '2015-01-01 00:00:00', p.ValordaReavaliacao, p.ValorAquisicao)";

        $dados = DB::connection('egap')->table('mat_itembaixa as ib')
            ->join('mat_baixa as b', 'ib.id_baixa', '=', 'b.id')
            ->join('mat_patrimonio as p', 'ib.id_bem', '=', 'p.id')
            ->join('mat_planocontas as pc', 'p.ContaContabil', '=', 'pc.id')
            ->join('mat_situacao as s', 'p.SituacaoBem', '=', 's.id')
            ->leftJoin('mat_descricaoresumida as dr', 'p.DescricaoResumidadoBem', '=', 'dr.id')
            ->leftJoin('mat_marca as ma', 'p.Marca', '=', 'ma.id')
            ->leftJoin('mat_modelo as mo', 'p.Modelo', '=', 'mo.id')
            ->whereBetween('p.DataBaixa', [$d->ini, $d->fim])->whereIn('p.SituacaoBem', [2, 3, 4, 5, 6])
            ->tap(fn($q) => $this->aplicarFiltros($q, $filtros, 'p.ContaContabil', 'p.situacao_contabil'))
            ->select('b.NumeroProcesso as processo', 'b.Requisitante as requisitante', 'b.RequisitanteCnpj as cnpj', 'b.DataBaixa as data_baixa_processo', 'b.Observacao as observacao', 'b.Endereco as endereco', 'pc.codigo as conta_contabil', 'p.NumPatrimonio as patrimonio', 'dr.Descricao as descricao', 'ma.Descricao as marca', 'mo.descricao as modelo', DB::raw("$valBruto as valor_bruto"), 'p.ValordaReavaliacao as valor_reavaliacao', 's.descricao as situacao')
            ->selectSub($subDepAcum, 'depreciacao_acumulada')->selectSub($subValorLiq, 'valor_liquido_calc')
            ->orderBy('b.NumeroProcesso')->orderBy('pc.codigo')->orderBy('p.NumPatrimonio')->get()
            ->map(fn($item) => (object)[... (array)$item, 'depreciacao_acumulada' => $item->depreciacao_acumulada ?? 0, 'valor_liquido' => $item->valor_liquido_calc ?? $item->valor_reavaliacao]);

        return $this->render('bens-baixados', null, $filtros, [
            'agrupadoPorProcesso' => $dados->groupBy('processo'),
            'resumoFinal' => $dados->groupBy(fn($i) => $i->conta_contabil . '|' . $i->processo)->map(fn($group) => (object) ['conta_contabil' => $group->first()->conta_contabil, 'processo' => $group->first()->processo, 'valor_bruto' => $group->sum('valor_bruto'), 'valor_liquido' => $group->sum('valor_liquido'), 'depreciacao_acumulada' => $group->sum('depreciacao_acumulada')])->sortBy('conta_contabil')->values()
        ]);
    }

    private function gerarBensBaixadosPorProcesso($filtros)
    {
        $d = $this->getPeriodo($filtros);

        $dados = DB::connection('egap')->table('mat_baixa as b')
            ->join('mat_itembaixa as ib', 'b.id', '=', 'ib.id_baixa')
            ->join('mat_patrimonio as p', 'ib.id_bem', '=', 'p.id')
            ->whereBetween('b.DataBaixa', [$d->ini, $d->fim])
            ->tap(fn($q) => $this->aplicarFiltros($q, $filtros, 'p.ContaContabil', 'p.situacao_contabil'))
            ->select('p.ProcessoBaixa as processo', DB::raw('COUNT(ib.id) as quantidade'), DB::raw('SUM(p.ValorAquisicao) as valor_aquisicao'), DB::raw('SUM(p.ValordaReavaliacao) as valor_reavaliado'))
            ->groupBy('p.ProcessoBaixa')->orderBy('p.ProcessoBaixa')->get();

        return $this->render('bens-baixados-por-processo', $dados, $filtros);
    }

    private function gerarBensConciliados($filtros)
    {
        $d = $this->getPeriodo($filtros);

        $dados = DB::connection('egap')->table('mat_patrimonio as p')
            ->join('mat_planocontas as pc', 'p.ContaContabil', '=', 'pc.id')
            ->join('mat_produtos as pr', 'p.Produto', '=', 'pr.id')
            ->select('pc.codigo as conta_contabil', 'pr.CodigodaClasse as cod_nat_despesa', 'pr.DescricaodaClasse as descricao', DB::raw("SUM(CASE WHEN p.DatadaReavaliacao BETWEEN '{$d->ini}' AND '{$d->fim}' AND p.SituacaoBem NOT IN (8,9) THEN ROUND(p.ValorAquisicao, 4) ELSE 0 END) as valor_historico"), DB::raw("SUM(CASE WHEN p.DatadaReavaliacao BETWEEN '{$d->ini}' AND '{$d->fim}' AND p.SituacaoBem NOT IN (8,9) THEN p.ValordaReavaliacao ELSE 0 END) as valor_reavaliado"))
            ->tap(fn($q) => $this->aplicarFiltros($q, $filtros, 'pc.id', 'p.situacao_contabil'))
            ->groupBy('pc.id', 'pr.id', 'pc.codigo', 'pr.CodigodaClasse', 'pr.DescricaodaClasse')
            ->orderBy('pc.codigo')->orderBy('pr.CodigodaClasse')->get()
            ->map(fn($item) => (object)[... (array)$item, 'perda_depreciacao' => $item->valor_reavaliado - $item->valor_historico]);

        return $this->render('bens-conciliados', $dados, $filtros);
    }

    private function gerarAnaliticoContabil($filtros)
    {
        $d = $this->getPeriodo($filtros);

        $dados = DB::connection('egap')->table('mat_patrimonio as p')
            ->leftJoin('mat_planocontas as c', 'p.ContaContabil', '=', 'c.id')
            ->whereBetween('p.DatadeIncorporacao', [$d->ini, $d->fim])
            ->tap(fn($q) => $this->aplicarFiltros($q, $filtros, 'p.ContaContabil', 'p.situacao_contabil'))
            ->select('c.codigo as conta_contabil', 'p.NumPatrimonio as patrimonio', 'p.DatadeIncorporacao as data_aquisicao', 'p.ValorAquisicao as valor_entrada', DB::raw('(IFNULL(p.VidaUtilSIAFi, 0) - IFNULL(p.UtilizacaodoBemMeses, 0)) as vida_util_remanescente'), 'p.DataDisponibilizacao as data_disponibilidade', 'p.Valor as valor_liquido_contabil', 'p.ValorResidual as valor_residual', 'p.ValordaReavaliacao as valor_reavaliado')
            ->orderBy('c.codigo')->orderBy('p.NumPatrimonio')->get();

        return $this->render('analitico-contabil', $dados, $filtros);
    }

    private function gerarDepreciacaoMensal($filtros)
    {
        $d = $this->getPeriodo($filtros);

        $subValor = DB::connection('egap')->table('mat_patrimonio as p')
            ->select('p.ContaContabil', 'p.Produto')->selectRaw("SUM(IF(p.ValordaReavaliacao > 0, ROUND(p.ValordaReavaliacao, 4), ROUND(p.ValorAquisicao, 4))) as valor")
            ->whereRaw("p.SituacaoBem NOT IN (8,9)")
            ->where(fn($q) => $q->where(fn($q1) => $q1->where('p.DatadeIncorporacao', '<', $d->ini)->whereIn('p.SituacaoBem', [1,7]))->orWhere(fn($q2) => $q2->where('p.DatadeIncorporacao', '<', $d->ini)->where('p.DataBaixa', '>=', $d->fim)->whereIn('p.SituacaoBem', [2,3,4,5,6]))->orWhere(fn($q3) => $q3->whereBetween('p.DatadeIncorporacao', [$d->ini, $d->fim])))
            ->groupBy('p.ContaContabil', 'p.Produto');

        $subDep = DB::connection('egap')->table('mat_depreciacao as d')
            ->join('mat_patrimonio as p', 'd.patrimonio', '=', 'p.id')
            ->whereBetween('d.data_calculo', [$d->iniDate, $d->fimDate])->where('d.item', '>', 1)
            ->groupBy('p.ContaContabil', 'p.Produto')
            ->select('p.ContaContabil', 'p.Produto', DB::raw('SUM(d.valor_residual) as valor_residual'), DB::raw('SUM(d.depreciacao_mensal) as depreciacao_mensal'), DB::raw('SUM(d.depreciacao_acumulada) as depreciacao_acumulada'));

        $subSaidas = DB::connection('egap')->table('mat_itembaixa as ib')
            ->join('mat_patrimonio as p', 'ib.id_bem', '=', 'p.id')
            ->whereBetween('p.DataBaixa', [$d->ini, $d->fim])->whereIn('p.SituacaoBem', [2,3,4,5,6])->groupBy('p.ContaContabil', 'p.Produto')
            ->select('p.ContaContabil', 'p.Produto', DB::raw("SUM(IFNULL((SELECT d.depreciacao_acumulada FROM mat_depreciacao d WHERE d.patrimonio = p.id AND DATE_FORMAT(d.data_calculo,'%m%Y') = DATE_FORMAT(p.DataBaixa + INTERVAL -1 MONTH,'%m%Y') LIMIT 1), 0)) as dep_acumulada_saidas"));

        $dados = DB::connection('egap')->table('mat_patrimonio as pat')
            ->join('mat_planocontas as pc', 'pat.ContaContabil', '=', 'pc.id')
            ->join('mat_produtos as prod', 'pat.Produto', '=', 'prod.id')
            ->leftJoinSub($subValor, 'v', fn($j) => $j->on('pc.id', '=', 'v.ContaContabil')->on('prod.id', '=', 'v.Produto'))
            ->leftJoinSub($subDep, 'd', fn($j) => $j->on('pc.id', '=', 'd.ContaContabil')->on('prod.id', '=', 'd.Produto'))
            ->leftJoinSub($subSaidas, 'ds', fn($j) => $j->on('pc.id', '=', 'ds.ContaContabil')->on('prod.id', '=', 'ds.Produto'))
            ->tap(fn($q) => $this->aplicarFiltros($q, $filtros, 'pc.id', 'pat.situacao_contabil'))
            ->select('pc.codigo as conta_contabil', 'prod.CodigodaClasse as cod_nat_despesa', 'prod.DescricaodaClasse as descricao', DB::raw('IFNULL(v.valor, 0) as valor_base'), DB::raw('IFNULL(d.valor_residual, 0) as valor_residual'), DB::raw('IFNULL(d.depreciacao_mensal, 0) as dep_mensal'), DB::raw('IFNULL(d.depreciacao_acumulada, 0) as dep_acumulada'), DB::raw('(IFNULL(v.valor, 0) - IFNULL(d.depreciacao_acumulada, 0)) as valor_liquido'), DB::raw('IFNULL(ds.dep_acumulada_saidas, 0) as dep_saidas'))
            ->distinct()->orderBy('pc.codigo')->orderBy('prod.CodigodaClasse')->get();

        return $this->render('depreciacao-mensal', $dados, $filtros);
    }

    private function gerarDepreciacaoMensalCc($filtros)
    {
        $d = $this->getPeriodo($filtros);

        $subValor = DB::connection('egap')->table('mat_patrimonio as p')
            ->select('p.ContaContabil', 'p.Produto', 'p.Setor')->selectRaw("SUM(IF(p.ValordaReavaliacao > 0, ROUND(p.ValordaReavaliacao, 4), ROUND(p.ValorAquisicao, 4))) as valor")
            ->whereRaw("p.SituacaoBem NOT IN (8,9)")
            ->where(fn($q) => $q->where(fn($q1) => $q1->where('p.DatadeIncorporacao', '<', $d->ini)->whereIn('p.SituacaoBem', [1,7]))->orWhere(fn($q2) => $q2->where('p.DatadeIncorporacao', '<', $d->ini)->where('p.DataBaixa', '>=', $d->fim)->whereIn('p.SituacaoBem', [2,3,4,5,6]))->orWhere(fn($q3) => $q3->whereBetween('p.DatadeIncorporacao', [$d->ini, $d->fim])))
            ->groupBy('p.ContaContabil', 'p.Produto', 'p.Setor');

        $subDep = DB::connection('egap')->table('mat_depreciacao as d')
            ->join('mat_patrimonio as p', 'd.patrimonio', '=', 'p.id')
            ->whereBetween('d.data_calculo', [$d->iniDate, $d->fimDate])->where('d.item', '>', 1)
            ->groupBy('p.ContaContabil', 'p.Produto', 'p.Setor')
            ->select('p.ContaContabil', 'p.Produto', 'p.Setor', DB::raw('SUM(d.valor_residual) as valor_residual'), DB::raw('SUM(d.depreciacao_mensal) as depreciacao_mensal'), DB::raw('SUM(d.depreciacao_acumulada) as depreciacao_acumulada'));

        $subSaidas = DB::connection('egap')->table('mat_itembaixa as ib')
            ->join('mat_patrimonio as p', 'ib.id_bem', '=', 'p.id')
            ->whereBetween('p.DataBaixa', [$d->ini, $d->fim])->whereIn('p.SituacaoBem', [2,3,4,5,6])->groupBy('p.ContaContabil', 'p.Produto', 'p.Setor')
            ->select('p.ContaContabil', 'p.Produto', 'p.Setor', DB::raw("SUM(IFNULL((SELECT d.depreciacao_acumulada FROM mat_depreciacao d WHERE d.patrimonio = p.id AND DATE_FORMAT(d.data_calculo,'%m%Y') = DATE_FORMAT(p.DataBaixa + INTERVAL -1 MONTH,'%m%Y') LIMIT 1), 0)) as dep_acumulada_saidas"));

        $dados = DB::connection('egap')->table('mat_patrimonio as pat')
            ->join('mat_planocontas as pc', 'pat.ContaContabil', '=', 'pc.id')
            ->join('mat_produtos as prod', 'pat.Produto', '=', 'prod.id')
            ->join('mat_setores as s', 'pat.Setor', '=', 's.id')
            ->join('cad_centrocusto as cc', 's.centrocusto', '=', 'cc.codigo')
            ->leftJoinSub($subValor, 'v', fn($j) => $j->on('pc.id', '=', 'v.ContaContabil')->on('prod.id', '=', 'v.Produto')->on('pat.Setor', '=', 'v.Setor'))
            ->leftJoinSub($subDep, 'd', fn($j) => $j->on('pc.id', '=', 'd.ContaContabil')->on('prod.id', '=', 'd.Produto')->on('pat.Setor', '=', 'd.Setor'))
            ->leftJoinSub($subSaidas, 'ds', fn($j) => $j->on('pc.id', '=', 'ds.ContaContabil')->on('prod.id', '=', 'ds.Produto')->on('pat.Setor', '=', 'ds.Setor'))
            ->tap(fn($q) => $this->aplicarFiltros($q, $filtros, 'pc.id', 'pat.situacao_contabil'))
            ->when($filtros['centro_custo'] ?? null, fn($q, $v) => $q->where('cc.codigo', $v))
            ->select('cc.codigo as cc_codigo', 'cc.descricao as cc_descricao', 'pc.codigo as conta_contabil', 'prod.item_patrimonial', 'prod.CodigodaClasse as cod_nat_despesa', 'prod.DescricaodaClasse as descricao', DB::raw('IFNULL(v.valor, 0) as valor_base'), DB::raw('IFNULL(d.valor_residual, 0) as valor_residual'), DB::raw('IFNULL(d.depreciacao_mensal, 0) as dep_mensal'), DB::raw('IFNULL(d.depreciacao_acumulada, 0) as dep_acumulada'), DB::raw('(IFNULL(v.valor, 0) - IFNULL(d.depreciacao_acumulada, 0)) as valor_liquido'), DB::raw('IFNULL(ds.dep_acumulada_saidas, 0) as dep_saidas'))
            ->distinct()->orderBy('cc.codigo')->orderBy('pc.codigo')->orderBy('prod.CodigodaClasse')->get();

        return $this->render('depreciacao-mensal-cc', null, $filtros, ['dadosAgrupados' => $dados->groupBy(fn($i) => $i->cc_codigo . ' ' . $i->cc_descricao)]);
    }

    private function gerarBensPatrimoniais($filtros)
    {
        $dados = DB::connection('egap')->table('mat_patrimonio as p')
            ->leftJoin('mat_marca as ma', 'p.Marca', '=', 'ma.id')->leftJoin('mat_modelo as mo', 'p.Modelo', '=', 'mo.id')
            ->leftJoin('mat_setores as se', 'p.Setor', '=', 'se.id')->leftJoin('mat_fornecedor as fo', 'p.Fornecedor', '=', 'fo.id')
            ->leftJoin('mat_descricaoresumida as de', 'p.DescricaoResumidadoBem', '=', 'de.id')
            ->select('p.NumPatrimonio as patrimonio', 'p.Descricao as descricao', 'ma.Descricao as marca', 'mo.descricao as modelo', 'se.Setor as setor', 'p.numero_processo as processo', 'p.NotaFiscal as nota_fiscal', 'p.ValorAquisicao as valor_aquisicao', 'p.DatadeIncorporacao as data_incorporacao', 'fo.NomeFornecedor as fornecedor', 'p.grupo', 'de.Descricao as desc_resumida')
            ->when($filtros['numero_processo'] ?? null, fn($q, $v) => $q->where('p.numero_processo', $v))
            ->when($filtros['nota_fiscal'] ?? null, fn($q, $v) => $q->where('p.NotaFiscal', $v))
            ->when($filtros['acuracia'] ?? null, fn($q, $v) => $q->where('p.acuracia', $v))
            ->tap(fn($q) => $this->aplicarFiltros($q, $filtros, null, 'p.situacao_contabil'))
            ->when($filtros['grupo'] ?? null, fn($q, $v) => $v === 'A' ? $q->where(fn($sq) => $sq->whereNotIn('p.grupo', ['B', 'C', 'D', 'E'])->orWhereNull('p.grupo')) : $q->where('p.grupo', $v))
            ->orderBy('se.Setor')->orderBy('p.NumPatrimonio')->get()
            ->map(function($i) {
                $g = $i->grupo;
                if ($g === 'B') $i->grupo_desc = 'Inventariado antes de 2015'; elseif ($g === 'C') $i->grupo_desc = 'Inventário Online'; elseif ($g === 'D') $i->grupo_desc = 'A inventariar'; elseif ($g === 'E') $i->grupo_desc = 'Baixados'; else $i->grupo_desc = 'Inventariado a partir de 2015';
                $i->desc_resumida = $i->desc_resumida ?: 'NÃO INFORMADA';
                return $i;
            });

        $resumo = $dados->groupBy('desc_resumida')->map(fn($itens, $desc) => (object) [
            'descricao' => $desc,
            'qtd_a' => $itens->where('grupo_desc', 'Inventariado a partir de 2015')->count(), 'val_a' => $itens->where('grupo_desc', 'Inventariado a partir de 2015')->sum('valor_aquisicao'),
            'qtd_b' => $itens->where('grupo_desc', 'Inventariado antes de 2015')->count(), 'val_b' => $itens->where('grupo_desc', 'Inventariado antes de 2015')->sum('valor_aquisicao'),
            'qtd_c' => $itens->where('grupo_desc', 'Inventário Online')->count(), 'val_c' => $itens->where('grupo_desc', 'Inventário Online')->sum('valor_aquisicao'),
            'qtd_d' => $itens->where('grupo_desc', 'A inventariar')->count(), 'val_d' => $itens->where('grupo_desc', 'A inventariar')->sum('valor_aquisicao'),
            'qtd_total' => $itens->count(),
        ])->sortBy('descricao')->values();

        return $this->render('bens-patrimoniais', $dados, $filtros, ['resumo' => $resumo, 'chartData' => $dados->groupBy('grupo_desc')->map->count()]);
    }

    private function gerarInventarioBensMoveis($filtros)
    {
        $d = $this->getPeriodo($filtros);

        $subDep = DB::connection('egap')->table('mat_depreciacao')->whereRaw("DATE_FORMAT(data_calculo, '%Y-%m') = ?", [$d->mesRef])->select('patrimonio', 'depreciacao_acumulada', 'valor_liquido_contabil');

        $dados = DB::connection('egap')->table('mat_patrimonio as p')
            ->join('mat_planocontas as pla', 'p.ContaContabil', '=', 'pla.id')->join('mat_setores as se', 'p.Setor', '=', 'se.id')
            ->leftJoinSub($subDep, 'dep', 'p.id', '=', 'dep.patrimonio')
            ->whereIn('p.SituacaoBem', [1, 7])->where('p.DatadeIncorporacao', '<=', $d->fim)
            ->whereRaw("YEAR(p.DatadaReavaliacao) <= ?", [$d->ano])
            ->tap(fn($q) => $this->aplicarFiltros($q, $filtros, 'p.ContaContabil', 'p.situacao_contabil'))
            ->select('pla.titulo as conta_titulo', 'pla.codigo as conta_codigo', 'p.NumPatrimonio as patrimonio', 'p.Descricao as descricao', 'p.DatadeIncorporacao as data_aquisicao', DB::raw('IF(IFNULL(p.ValordaReavaliacao, 0) > 0, p.ValordaReavaliacao, p.ValorAquisicao) as valor_reavaliado'), DB::raw('IFNULL(dep.depreciacao_acumulada, 0) as depreciacao_acumulada'), DB::raw('IFNULL(dep.valor_liquido_contabil, 0) as valor_liquido'), 'se.Setor as setor')
            ->orderBy('pla.titulo')->orderBy('p.NumPatrimonio')->get();

        return $this->render('inventario-bens-moveis', $dados, $filtros, ['ano_inventario' => $d->ano]);
    }

    private function gerarInventarioBensMoveisDetalhado($filtros)
    {
        $d = $this->getPeriodo($filtros);

        $dados = DB::connection('egap')->table('mat_patrimonio as p')
            ->join('mat_planocontas as pla', 'p.ContaContabil', '=', 'pla.id')->join('mat_setores as se', 'p.Setor', '=', 'se.id')
            ->whereIn('p.SituacaoBem', [1, 7])->where('p.DatadeIncorporacao', '<=', $d->fim)
            ->whereRaw("YEAR(p.DatadaReavaliacao) <= ?", [$d->ano])
            ->tap(fn($q) => $this->aplicarFiltros($q, $filtros, 'p.ContaContabil', 'p.situacao_contabil'))
            ->select('pla.titulo as conta_titulo', 'pla.codigo as conta_codigo', 'p.NumPatrimonio as patrimonio', 'p.Descricao as descricao', 'p.DatadeIncorporacao as data_aquisicao', DB::raw('IFNULL(p.ValorAquisicao, 0) as valor_aquisicao'), DB::raw('IFNULL(p.ValordaReavaliacao, 0) as valor_ajustado'), DB::raw('IF(IFNULL(p.ValordaReavaliacao, 0) > 0, p.ValordaReavaliacao, p.ValorAquisicao) as valor_atual'), 'se.Setor as setor')
            ->orderBy('pla.titulo')->orderBy('p.NumPatrimonio')->get();

        return $this->render('inventario-bens-moveis-detalhado', $dados, $filtros, ['ano_inventario' => $d->ano]);
    }

    private function gerarRelacaoBensImoveis($filtros)
    {
        $d = $this->getPeriodo($filtros);

        $subRea = DB::connection('egap')->table('imo_reavaliacao as r')
            ->joinSub(DB::connection('egap')->table('imo_reavaliacao')->select('Id_imovel', DB::raw('MAX(data_reavaliacao) as max_data'))->where('data_reavaliacao', '<=', $d->fim)->groupBy('Id_imovel'), 'rm', fn($j) => $j->on('r.Id_imovel', '=', 'rm.Id_imovel')->on('r.data_reavaliacao', '=', 'rm.max_data'))
            ->select('r.Id_imovel', 'r.valor_reavaliacao', 'r.data_reavaliacao');

        $dados = DB::connection('egap')->table('imo_imovel as imo')
            ->leftJoin('imo_estadoconservacao as est', 'imo.Id_estadoconservacao', '=', 'est.Id')
            ->leftJoin('mat_planocontas as pla', 'imo.Id_planocontas', '=', 'pla.id')
            ->leftJoin('imo_situacao as sit', 'imo.Id_situacao', '=', 'sit.id')
            ->leftJoinSub($subRea, 'rea', 'imo.id', '=', 'rea.Id_imovel')
            ->leftJoinSub($this->subObrasImoveis($d->fim), 'obras', 'imo.id', '=', 'obras.id_imovel')
            ->where(fn($q) => $q->whereNull('imo.data_baixa')->orWhere('imo.data_baixa', '0000-00-00 00:00:00'))
            ->tap(fn($q) => $this->aplicarFiltros($q, $filtros, 'imo.Id_planocontas', null))
            ->select('sit.Descricao as situacao_imovel', 'imo.num_registro', 'imo.descricao as denominacao', 'imo.data_aquisicao', 'imo.data_construcao', 'imo.data_incorporacao', 'est.descEstadoConservacao as estado_conservacao', 'pla.codigo as conta_contabil', 'imo.data_ingresso_contabil', 'imo.inscricao_generica', 'imo.end_logradouro', 'imo.end_numero', 'imo.end_bairro', 'imo.end_cidade', 'imo.end_estado', 'imo.end_compl_endereco', 'imo.area', 'imo.area_edificacao', 'imo.vida_util', 'imo.valor_historico_1a_avaliacao', 'rea.data_reavaliacao', DB::raw('IFNULL(rea.valor_reavaliacao, imo.valor_reavaliado) as valor_reavaliado'), DB::raw('IFNULL(obras.valor_obra, 0) as valor_obra'))
            ->orderBy('imo.id_situacao')->orderBy('imo.Id_planocontas')->get()
            ->map(function($item) {
                $item->valor_atualizado = $item->valor_reavaliado + $item->valor_obra;
                $dates = [];
                if ($item->data_aquisicao && $item->data_aquisicao != '0000-00-00 00:00:00') $dates[] = \Carbon\Carbon::parse($item->data_aquisicao)->format('d/m/Y');
                if ($item->data_construcao && $item->data_construcao != '0000-00-00 00:00:00') $dates[] = \Carbon\Carbon::parse($item->data_construcao)->format('d/m/Y');
                if ($item->data_incorporacao && $item->data_incorporacao != '0000-00-00 00:00:00') $dates[] = \Carbon\Carbon::parse($item->data_incorporacao)->format('d/m/Y');
                $item->datas_concat = implode('/<br>', $dates);
                $addr = array_filter([$item->end_logradouro, $item->end_numero, $item->end_bairro, $item->end_cidade, $item->end_estado, $item->end_compl_endereco]);
                $item->endereco = implode(', ', $addr);
                $item->situacao_imovel = $item->situacao_imovel ?: 'Não Classificado';
                return $item;
            });

        return $this->render('relacao-bens-imoveis', null, $filtros, ['dadosAgrupados' => $dados->groupBy('situacao_imovel')]);
    }

    private function gerarDepreciacaoMensalImoveis($filtros)
    {
        $d = $this->getPeriodo($filtros);

        $dados = DB::connection('egap')->table('imo_imovel as imo')
            ->join('mat_planocontas as pc', 'imo.Id_planocontas', '=', 'pc.id')
            ->join('imo_depreciacao as d', 'imo.id', '=', 'd.Id_imovel')
            ->leftJoinSub($this->subObrasImoveis($d->fim), 'obras', 'imo.id', '=', 'obras.id_imovel')
            ->where('imo.Id_situacao', 1)->where('d.item', '>', 1)->whereBetween('d.data_calculo', [$d->iniDate, $d->fimDate])
            ->tap(fn($q) => $this->aplicarFiltros($q, $filtros, 'imo.Id_planocontas', null))
            ->select('pc.codigo as conta_contabil', 'pc.titulo as descricao_subitem', 'imo.num_registro', 'imo.descricao as descricao_imovel', 'imo.inscricao_generica', DB::raw('(d.valor + IFNULL(obras.valor_obra, 0)) as valor_atual'), 'd.valor_residual', 'd.depreciacao_mensal', 'd.depreciacao_acumulada', DB::raw('(d.valor_liquido_contabil + IFNULL(obras.valor_obra, 0)) as valor_liquido'))
            ->distinct()->orderBy('pc.codigo')->orderBy('imo.descricao')->get();

        return $this->render('depreciacao-mensal-imoveis', $dados, $filtros);
    }

    private function gerarDepreciacaoMensalImoveisCc($filtros)
    {
        $d = $this->getPeriodo($filtros);

        $dados = DB::connection('egap')->table('imo_imovel as imo')
            ->join('mat_planocontas as pc', 'imo.Id_planocontas', '=', 'pc.id')
            ->join('imo_depreciacao as d', 'imo.id', '=', 'd.Id_imovel')
            ->join('mat_setores as s', 'imo.Id_Setores', '=', 's.id')
            ->join('cad_centrocusto as cc', 's.centrocusto', '=', 'cc.codigo')
            ->leftJoinSub($this->subObrasImoveis($d->fim), 'obras', 'imo.id', '=', 'obras.id_imovel')
            ->where('imo.Id_situacao', 1)->where('d.item', '>', 1)->whereBetween('d.data_calculo', [$d->iniDate, $d->fimDate])
            ->tap(fn($q) => $this->aplicarFiltros($q, $filtros, 'imo.Id_planocontas', null))
            ->when($filtros['centro_custo'] ?? null, fn($q, $v) => $q->where('cc.codigo', $v))
            ->select('cc.codigo as cc_codigo', 'cc.descricao as cc_descricao', 'pc.codigo as conta_contabil', 'pc.titulo as descricao_subitem', 'imo.num_registro', 'imo.descricao as descricao_imovel', 'imo.inscricao_generica', DB::raw('(d.valor + IFNULL(obras.valor_obra, 0)) as valor_atual'), 'd.valor_residual', 'd.depreciacao_mensal', 'd.depreciacao_acumulada', DB::raw('(d.valor_liquido_contabil + IFNULL(obras.valor_obra, 0)) as valor_liquido'))
            ->distinct()->orderBy('cc.codigo')->orderBy('pc.codigo')->orderBy('imo.descricao')->get();

        return $this->render('depreciacao-mensal-imoveis-cc', null, $filtros, ['dadosAgrupados' => $dados->groupBy(fn($i) => $i->cc_codigo . ' ' . $i->cc_descricao)]);
    }

    private function gerarInventarioBensImoveis($filtros)
    {
        $d = $this->getPeriodo($filtros);

        $dados = DB::connection('egap')->table('imo_imovel as p')
            ->join('mat_planocontas as pla', 'p.Id_planocontas', '=', 'pla.id')
            ->leftJoinSub($this->subDepreciacaoImoveis($d->mesRef), 'dep', 'p.id', '=', 'dep.Id_imovel')
            ->leftJoinSub($this->subObrasImoveis($d->fimDate), 'obras', 'p.id', '=', 'obras.id_imovel')
            ->where(fn($q) => $q->whereNull('p.data_baixa')->orWhere('p.data_baixa', '0000-00-00 00:00:00'))
            ->tap(fn($q) => $this->aplicarFiltros($q, $filtros, 'p.Id_planocontas', null))
            ->select('pla.codigo as conta_codigo', 'pla.titulo as conta_titulo', 'p.num_registro as patrimonio', 'p.descricao', 'p.inscricao_generica', 'p.inscricao_imobiliaria', 'p.data_incorporacao as data_aquisicao', 'p.end_logradouro', 'p.end_numero', 'p.end_bairro', 'p.end_cidade', 'p.end_estado', 'p.valor_reavaliado as imo_val_reavaliado', 'obras.valor_obra', 'dep.depreciacao_acumulada as dep_acumulada', 'p.depreciacao_acumulada as imo_dep_acumulada', 'dep.valor_liquido_contabil as dep_val_liquido', 'p.valor_liquido_contabil as imo_val_liquido')
            ->orderBy('pla.codigo')->orderBy('pla.titulo')->orderBy('p.num_registro')->get()
            ->map(function ($item) {
                $valReavaliadoCalc = ($item->imo_val_reavaliado ?? 0) + ($item->valor_obra ?? 0);
                $item->valor_historico = $valReavaliadoCalc;
                $item->depreciacao = $item->dep_acumulada ?? $item->imo_dep_acumulada ?? 0;
                $baseLiquido = $item->imo_val_liquido > 0 ? $item->imo_val_liquido : $valReavaliadoCalc;
                $item->valor_contabil = ($item->dep_val_liquido ?? $baseLiquido) + ($item->valor_obra ?? 0);
                
                $num = ($item->end_numero && strtolower($item->end_numero) != 'null') ? $item->end_numero : 's/n';
                $bairro = ($item->end_bairro && strtolower($item->end_bairro) != 'null') ? $item->end_bairro : 'Centro';
                $item->localizacao = implode(', ', array_filter([$item->end_logradouro, $num, $bairro, $item->end_cidade, $item->end_estado]));
                return $item;
            });

        return $this->render('inventario-bens-imoveis', null, $filtros, ['dadosAgrupados' => $dados->groupBy(fn($i) => $i->conta_codigo . ' - ' . $i->conta_titulo), 'ano' => $d->ano]);
    }

    private function gerarAjustesReavaliacaoImoveis($filtros)
    {
        $d = $this->getPeriodo($filtros);

        $subDep = DB::connection('egap')->table('imo_depreciacao')->select('Id_imovel', 'valor', 'depreciacao_acumulada', 'valor_liquido_contabil')
            ->whereRaw("DATE_FORMAT(data_calculo, '%Y-%m') = ?", [$d->mesAnt])->where('depreciacao_acumulada', '>', 0);

        $dados = DB::connection('egap')->table('imo_reavaliacao as rea')
            ->join('imo_imovel as imo', 'rea.Id_imovel', '=', 'imo.id')
            ->join('mat_planocontas as pc', 'imo.Id_planocontas', '=', 'pc.id')
            ->leftJoinSub($subDep, 'dep', 'imo.id', '=', 'dep.Id_imovel')
            ->where('imo.Id_situacao', 1)->whereBetween('rea.data_reavaliacao', [$d->ini, $d->fim])
            ->tap(fn($q) => $this->aplicarFiltros($q, $filtros, 'imo.Id_planocontas', null))
            ->select('pc.codigo as conta_contabil', 'pc.titulo as descricao_subitem', 'imo.id as imovel_id', 'imo.inscricao_generica', 'imo.num_registro', 'imo.descricao as descricao_imovel', 'imo.valor_historico_1a_avaliacao', 'dep.valor as dep_valor', DB::raw('IFNULL(dep.depreciacao_acumulada, 0) as depreciacao_acumulada'), DB::raw('IFNULL(dep.valor_liquido_contabil, 0) as valor_liquido_contabil'), 'rea.data_reavaliacao', 'rea.valor_reavaliacao as valor_atual', 'rea.ajuste_contabil')
            ->orderBy('pc.codigo')->orderBy('imo.descricao')->get()
            ->map(function ($item) {
                $valorBrutoReavaliacao = DB::connection('egap')->table('imo_reavaliacao')->where('Id_imovel', $item->imovel_id)->orderByDesc('data_reavaliacao')->skip(1)->take(1)->value('valor_reavaliacao');
                $valBruto = $item->valor_historico_1a_avaliacao;
                if ($valorBrutoReavaliacao > 0) $valBruto = $valorBrutoReavaliacao;
                if ($item->dep_valor > 0) $valBruto = $item->dep_valor;
                $item->valor_bruto = $valBruto;
                return $item;
            });

        return $this->render('ajustes-reavaliacao-imoveis', $dados, $filtros);
    }

    private function gerarSaldoAnteriorImoveis($filtros)
    {
        $d = $this->getPeriodo($filtros);

        $reavaliacoes = DB::connection('egap')->table('imo_reavaliacao')->where('data_reavaliacao', '<=', $d->fim)->orderBy('data_reavaliacao', 'asc')->get()->groupBy('Id_imovel');
        $obras = DB::connection('egap')->table('imo_obras')->where('data', '<=', $d->fim)->get()->groupBy('id_imovel');

        $dados = DB::connection('egap')->table('imo_imovel as imo')
            ->join('imo_situacao as sit', 'imo.Id_situacao', '=', 'sit.id')->where('imo.Id_situacao', 1)
            ->where(fn ($q) => $q->whereNull('imo.data_aquisicao')->orWhere('imo.data_aquisicao', '<=', $d->fim))
            ->where(fn ($q) => $q->where('imo.data_situacao', '0000-00-00 00:00:00')->orWhereNull('imo.data_situacao')->orWhere('imo.data_situacao', '<=', $d->fim))
            ->where(fn ($q) => $q->where('imo.data_baixa', '>=', $d->fim)->orWhere('imo.data_baixa', '0000-00-00 00:00:00')->orWhereNull('imo.data_baixa'))
            ->tap(fn($q) => $this->aplicarFiltros($q, $filtros, 'imo.Id_planocontas', null))
            ->select('imo.id', 'imo.descricao', 'imo.data_aquisicao', 'imo.valor_historico_1a_avaliacao as valor_historico', 'imo.data_baixa', 'imo.data_situacao', 'sit.Descricao as situacao_descricao')
            ->orderBy('imo.id')->get()
            ->map(function ($imo) use ($reavaliacoes, $obras, $d) {
                $ultRea = collect($reavaliacoes->get($imo->id))->last();
                $reaData = null; $valReavaliacao = 0; $ajusteEntrada = 0; $ajusteSaida = 0;

                if ($ultRea && $ultRea->data_reavaliacao >= $d->ini && $ultRea->data_reavaliacao <= $d->fim) {
                    $reaData = $ultRea->data_reavaliacao;
                    $valReavaliacao = $ultRea->valor_reavaliacao;
                    $ajusteEntrada = $ultRea->ajuste_contabil >= 0 ? $ultRea->ajuste_contabil : 0;
                    $ajusteSaida = $ultRea->ajuste_contabil < 0 ? abs($ultRea->ajuste_contabil) : 0;
                }

                $obrasImovel = collect($obras->get($imo->id));
                $imo->valorobra_entrada = $obrasImovel->whereBetween('data', [$d->ini, $d->fim])->sum('valor');

                if ($reaData) {
                    $imo->saldo_anterior = $valReavaliacao + $obrasImovel->whereBetween('data', [$reaData, $d->fim])->sum('valor');
                } else {
                    $imo->saldo_anterior = $imo->valor_historico + $obrasImovel->sum('valor');
                }

                $imo->ajustecontabil_entrada = $ajusteEntrada;
                $imo->ajustecontabil_saida = $ajusteSaida;
                $imo->data_reavaliacao = $reaData;
                $imo->valor_reavaliacao = $valReavaliacao;
                $imo->data_baixa = (!empty($imo->data_baixa) && $imo->data_baixa != '0000-00-00 00:00:00') ? $imo->data_baixa : null;
                $imo->data_situacao = (!empty($imo->data_situacao) && $imo->data_situacao != '0000-00-00 00:00:00') ? $imo->data_situacao : null;

                return $imo;
            });

        return $this->render('saldo-anterior-imoveis', $dados, $filtros, ['inicioRaw' => $d->ini, 'terminoRaw' => $d->fim]);
    }

    private function gerarInventarioBensIntangiveis($filtros)
    {
        $d = $this->getPeriodo($filtros);

        $dados = DB::connection('egap')->table('int_intangivel as p')
            ->join('mat_planocontas as pla', 'p.id_planocontas', '=', 'pla.id')
            ->tap(fn($q) => $this->aplicarFiltros($q, $filtros, 'pla.id', null))
            ->select(
                'pla.codigo as conta_codigo',
                'pla.titulo as conta_titulo',
                'p.inscricao_generica',
                'p.nome as descricao',
                'p.data_aquisicao',
                'p.quantidade',
                'p.valor_aquisicao',
                'p.amortizacao_acumulada',
                'p.valor_liquido_contabil',
                'p.vida_util_remanescente'
            )
            ->orderBy('pla.codigo')
            ->orderBy('pla.titulo')
            ->orderBy('p.inscricao_generica')
            ->get();

        $dadosAgrupados = $dados->groupBy(function($item) {
            return $item->conta_codigo . ' - ' . $item->conta_titulo;
        });

        return $this->render('inventario-bens-intangiveis', null, $filtros, [
            'dadosAgrupados' => $dadosAgrupados,
            'ano' => $d->ano
        ]);
    }

    private function gerarNotasFiscaisPorFornecedor($filtros)
    {
        $d = $this->getPeriodo($filtros);

        $dados = DB::connection('egap')->table('alm_notafiscal as nf')
            ->leftJoin('mat_fornecedor as fnd', 'nf.fornecedor', '=', 'fnd.id')
            ->leftJoin('alm_itens_notafiscal as inf', 'nf.id', '=', 'inf.id_notafiscal')
            ->leftJoin('mat_descricaodetalhada as dd', 'inf.id_material', '=', 'dd.id')
            ->leftJoin('mat_descricaoresumida as dr', 'dd.descricao_resumida', '=', 'dr.id')
            ->leftJoin('mat_produtos as el', 'dr.id_produto', '=', 'el.id')
            ->whereBetween('nf.date_time', [$d->ini, $d->fim])
            ->select(
                'fnd.id as id_fornecedor', 
                'fnd.NomeFornecedor as fornecedor', 
                DB::raw('IFNULL(fnd.CNPJ, "0") as cnpj'),
                'nf.id as id_notafiscal', 
                'nf.num_documento', 
                'nf.data_documento', 
                'nf.tipo_documento',
                DB::raw('ROUND(inf.quantidade, 0) as quantidade'), 
                'inf.preco_unitario', 
                'inf.total_item as valor_total',
                'dd.descricao_detalhada', 
                'dr.Descricao as descricao_resumida',
                'el.CodigodaClasse as elemento_codigo'
            )
            ->orderBy('fnd.CNPJ')
            ->orderBy('nf.num_documento')
            ->get()
            ->map(function($item) {
                $v = preg_replace('/\D/', '', $item->cnpj);
                if (strlen($v) === 11) {
                    $item->cnpj_formatado = preg_replace('/(\d{3})(\d{3})(\d{3})(\d{2})/', '$1.$2.$3-$4', $v);
                } elseif (strlen($v) === 14) {
                    $item->cnpj_formatado = preg_replace('/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/', '$1.$2.$3/$4-$5', $v);
                } else {
                    $item->cnpj_formatado = $item->cnpj ?: '0';
                }
                
                $item->tipo_doc_desc = $item->tipo_documento == 1 ? 'Nota Fiscal' : 'Nota Fiscal/Doação';
                $item->data_doc_formatada = $item->data_documento ? Carbon::parse($item->data_documento)->format('d/m/Y') : '';
                return $item;
            });

        $resumo = (object) [
            'qtde_notas'  => $dados->unique('id_notafiscal')->count(),
            'qtde_itens'  => $dados->sum('quantidade'),
            'total_notas' => $dados->sum('valor_total')
        ];

        return $this->render('notas-fiscais-fornecedor', $dados, $filtros, [
            'resumo' => $resumo
        ]);
    }

    private function gerarBalanceteContabilAnalitico($filtros)
    {
        $d = $this->getPeriodo($filtros);

        $subSa = DB::connection('egap')->table('alm_estoque as est')
            ->joinSub(
                DB::connection('egap')->table('alm_estoque')
                    ->select('material', DB::raw('MAX(id) as max_id'))
                    ->where('date_time', '<', $d->iniDate)
                    ->groupBy('material'),
                'ult', 'est.id', '=', 'ult.max_id'
            )
            ->select('est.material', 'est.quantidade_estoque', 'est.valor_total_estoque');

        $subEntradas = DB::connection('egap')->table('alm_estoque')
            ->select('material', DB::raw('SUM(quantidade) as qtd'), DB::raw('SUM(valor_total) as valor'))
            ->where('tipo_movimentacao', 1)
            ->whereBetween('date_time', [$d->ini, $d->fim])
            ->groupBy('material');

        $subSaidas = DB::connection('egap')->table('alm_estoque')
            ->select('material', DB::raw('SUM(quantidade) as qtd'), DB::raw('SUM(valor_total) as valor'))
            ->where('tipo_movimentacao', 2)
            ->whereBetween('date_time', [$d->ini, $d->fim])
            ->groupBy('material');

        $dados = DB::connection('egap')->table('mat_descricaodetalhada as dd')
            ->join('mat_descricaoresumida as dr', 'dd.descricao_resumida', '=', 'dr.id')
            ->join('mat_produtos as el', 'dr.id_produto', '=', 'el.id')
            ->leftJoinSub($subSa, 'sa', 'dd.id', '=', 'sa.material')
            ->leftJoinSub($subEntradas, 'ent', 'dd.id', '=', 'ent.material')
            ->leftJoinSub($subSaidas, 'sai', 'dd.id', '=', 'sai.material')
            ->where(function($q) {
                $q->where('sa.quantidade_estoque', '>', 0)
                  ->orWhere('ent.qtd', '>', 0)
                  ->orWhere('sai.qtd', '>', 0);
            })
            ->whereIn('dr.id_tipo_material', ['C', 'D', 'P'])
            ->when($filtros['conta_contabil'] ?? null, fn($q, $v) => $q->where('dr.ContaContabil', $v))
            ->select(
                'dd.descricao_detalhada',
                'dr.id_tipo_material',
                'el.CodigodaClasse as elemento',
                DB::raw('IFNULL(sa.quantidade_estoque, 0) as sa_qtd'),
                DB::raw('IFNULL(sa.valor_total_estoque, 0) as sa_valor'),
                DB::raw('IFNULL(ent.qtd, 0) as ent_qtd'),
                DB::raw('IFNULL(ent.valor, 0) as ent_valor'),
                DB::raw('IFNULL(sai.qtd, 0) as sai_qtd'),
                DB::raw('IFNULL(sai.valor, 0) as sai_valor')
            )
            ->orderBy('dr.id_tipo_material')
            ->orderBy('dd.descricao_detalhada')
            ->get()
            ->map(function($item) {
                if ($item->id_tipo_material === 'C') {
                    $item->tipo_desc = 'Consumo';
                    $item->ordem = 1;
                } elseif ($item->id_tipo_material === 'D') {
                    $item->tipo_desc = 'Consumo Durável';
                    $item->ordem = 2;
                } else {
                    $item->tipo_desc = 'Permanente';
                    $item->ordem = 3;
                }

                $item->atual_qtd = $item->sa_qtd + $item->ent_qtd - $item->sai_qtd;
                $item->atual_valor = $item->sa_valor + $item->ent_valor - $item->sai_valor;
                return $item;
            });

        $dadosAgrupados = $dados->sortBy('ordem')->groupBy('tipo_desc');

        return $this->render('balancete-contabil-analitico', null, $filtros, [
            'dadosAgrupados' => $dadosAgrupados
        ]);
    }

    private function gerarMediaConsumoMaterial($filtros)
    {
        $d = $this->getPeriodo($filtros);

        $dias = $d->objIni->diffInDays($d->objFim);
        $meses = floor($dias / 30) + (($dias % 30 > 20) ? 1 : 0);
        $mesesCalc = $meses == 0 ? 1 : $meses;

        $subConsumo = DB::connection('egap')->table('alm_estoque')
            ->select('material', DB::raw('SUM(quantidade) as qtd_consumida'))
            ->where('tipo_movimentacao', 2)
            ->whereBetween('date_time', [$d->ini, $d->fim])
            ->groupBy('material');

        $subAtual = DB::connection('egap')->table('alm_estoque as e1')
            ->joinSub(
                DB::connection('egap')->table('alm_estoque')->select('material', DB::raw('MAX(id) as max_id'))->groupBy('material'),
                'e2', 'e1.id', '=', 'e2.max_id'
            )
            ->select('e1.material', 'e1.quantidade_estoque');

        $dados = DB::connection('egap')->table('mat_descricaodetalhada as dd')
            ->joinSub($subAtual, 'est', 'dd.id', '=', 'est.material')
            ->leftJoinSub($subConsumo, 'con', 'dd.id', '=', 'con.material')
            ->where('dd.item_estoque', 1)
            ->when(!empty($filtros['materiais']), function($q) use ($filtros) {
                $q->whereIn('dd.id', $filtros['materiais']);
            })
            ->select(
                'dd.id',
                'dd.descricao_detalhada',
                DB::raw('IFNULL(est.quantidade_estoque, 0) as qtde_atual'),
                DB::raw('IFNULL(con.qtd_consumida, 0) as qtde_consumida')
            )
            ->orderBy('dd.descricao_detalhada')
            ->get()
            ->map(function($item) use ($mesesCalc, $meses, $dias) {
                $item->consumo_medio = $item->qtde_consumida / $mesesCalc;
                $item->meses = $meses;
                $item->dias = $dias;
                return $item;
            });

        return $this->render('media-consumo-material', $dados, $filtros);
    }

    private function gerarPedidosValidadosSetor($filtros)
    {
        $d = $this->getPeriodo($filtros);

        $dados = DB::connection('egap')->table('ped_pedidos as ped')
            ->leftJoin('jos_users as usr', 'ped.Solicitante', '=', 'usr.id')
            ->leftJoin('mat_setores as se', 'ped.Setor', '=', 'se.id')
            ->join('ped_itempedido as iped', 'ped.id', '=', 'iped.idPedido')
            ->leftJoin('mat_descricaodetalhada as dd', 'iped.DescricaoDetalhada', '=', 'dd.id')
            ->leftJoin('mat_descricaoresumida as dr', 'dd.descricao_resumida', '=', 'dr.id')
            ->leftJoin('mat_produtos as el', 'dr.id_produto', '=', 'el.id')
            ->leftJoin('mat_unidades as un', 'dd.unidade_medida', '=', 'un.id')
            ->leftJoin('alm_estoque as est', function($join) {
                $join->on('iped.idPedido', '=', 'est.id_pedido')
                     ->on('dd.id', '=', 'est.material');
            })
            ->where('ped.setor_responsavel', 799)
            ->where('ped.idSituacao', 7)
            ->whereBetween('ped.date_time', [$d->ini, $d->fim])
            ->select(
                'ped.id as pedido_id',
                'se.Setor as setor',
                'usr.name as solicitante',
                'dr.Descricao as descricao_resumida',
                'dd.descricao_detalhada',
                'el.CodigodaClasse as elemento',
                'un.Sigla as sigla',
                'un.Unidade as unidade',
                'iped.QuantidadeMaterial as qtde_solicitada',
                'iped.QuantidadeMaterialAtendida as qtde_atendida',
                DB::raw('ROUND(IFNULL(est.preco_medio_estoque, 0), 4) as valor_medio')
            )
            ->orderBy('ped.id')
            ->get()
            ->map(function($item) {
                $item->numero_formatado = str_pad($item->pedido_id, 8, '0', STR_PAD_LEFT);
                $item->valor_total = $item->qtde_atendida * $item->valor_medio;
                return $item;
            });

        $dadosAgrupados = $dados->groupBy('pedido_id');

        return $this->render('pedidos-validados-setor', null, $filtros, [
            'dadosAgrupados' => $dadosAgrupados
        ]);
    }

    private function gerarGastoAnualItensEstoque($filtros)
    {
        $d = $this->getPeriodo($filtros);

        $dados = DB::connection('egap')->table('alm_estoque as est')
            ->join('mat_descricaodetalhada as dd', 'est.material', '=', 'dd.id')
            ->join('mat_descricaoresumida as dr', 'dd.descricao_resumida', '=', 'dr.id')
            ->join('mat_produtos as pla', 'dr.id_produto', '=', 'pla.id')
            ->leftJoin('mat_unidades as un', 'un.id', '=', 'dd.unidade_medida')
            ->whereBetween('est.date_time', [$d->ini, $d->fim])
            ->where('dd.item_estoque', 1)
            ->where('est.tipo_movimentacao', 2)
            ->tap(fn($q) => $this->aplicarFiltros($q, $filtros, 'dr.ContaContabil', null))
            ->select(
                DB::raw('CONCAT(pla.CodigodaClasse, " - ", pla.DescricaodaClasse) as elemento_despesa'),
                DB::raw('CONCAT(dd.descricao_detalhada, " (", un.Sigla, ")") as material'),
                DB::raw('SUM(est.quantidade) as qtde'),
                DB::raw('SUM(est.valor_total) as valor')
            )
            ->groupBy('pla.CodigodaClasse', 'pla.DescricaodaClasse', 'dd.descricao_detalhada', 'un.Sigla')
            ->orderBy('elemento_despesa')
            ->orderBy('material')
            ->get();

        $dadosAgrupados = $dados->groupBy('elemento_despesa');

        return $this->render('gasto-anual-itens-estoque', null, $filtros, [
            'dadosAgrupados' => $dadosAgrupados
        ]);
    }

    private function gerarConsumoMaterialSubelemento($filtros)
    {
        $d = $this->getPeriodo($filtros);

        $subConsumo = DB::connection('egap')->table('alm_estoque')
            ->select('material', DB::raw('SUM(quantidade) as qtde_consumida'))
            ->where('tipo_movimentacao', 2)
            ->whereBetween('date_time', [$d->ini, $d->fim])
            ->groupBy('material');

        $subUltimoPreco = DB::connection('egap')->table('alm_estoque as e')
            ->joinSub(
                DB::connection('egap')->table('alm_estoque')->select('material', DB::raw('MAX(id) as max_id'))->groupBy('material'),
                'ult', 'e.id', '=', 'ult.max_id'
            )
            ->select('e.material', 'e.preco_unitario as ultimo_preco');

        $dados = DB::connection('egap')->table('mat_descricaodetalhada as dd')
            ->join('mat_descricaoresumida as dr', 'dd.descricao_resumida', '=', 'dr.id')
            ->join('mat_produtos as el', 'dr.id_produto', '=', 'el.id')
            ->joinSub($subConsumo, 'con', 'dd.id', '=', 'con.material')
            ->leftJoinSub($subUltimoPreco, 'preco', 'dd.id', '=', 'preco.material')
            ->where('dd.item_estoque', 1)
            ->tap(fn($q) => $this->aplicarFiltros($q, $filtros, 'dr.ContaContabil', null))
            ->select(
                'el.CodigodaClasse as subelemento',
                'dd.id as id_descricao',
                'dd.descricao_detalhada',
                'con.qtde_consumida',
                DB::raw('IFNULL(preco.ultimo_preco, 0) as ultimo_preco')
            )
            ->orderBy('el.CodigodaClasse')
            ->orderBy('dd.descricao_detalhada')
            ->get()
            ->map(function ($item) {
                $str = preg_replace('/\D/', '', $item->subelemento);
                if (strlen($str) >= 8) {
                    $item->subelemento_formatado = substr($str, 0, 1) . '.' . substr($str, 1, 1) . '.' . substr($str, 2, 2) . '.' . substr($str, 4, 2) . '.' . substr($str, 6, 2);
                } else {
                    $item->subelemento_formatado = $item->subelemento;
                }
                
                $item->subtotal = $item->qtde_consumida * $item->ultimo_preco;
                return $item;
            });

        $dadosAgrupados = $dados->groupBy('subelemento_formatado');

        return $this->render('consumo-material-subelemento', null, $filtros, [
            'dadosAgrupados' => $dadosAgrupados
        ]);
    }

    private function gerarResumoInventarioAlmoxarifadoCc($filtros)
    {
        $d = $this->getPeriodo($filtros);
        $inicioano = $d->objIni->copy()->startOfYear()->format('Y-m-d 00:00:00');

        $ccFiltro = $filtros['centro_custo'] ?? null;

        $subSa = DB::connection('egap')->table('alm_estoque as est')
            ->joinSub(
                DB::connection('egap')->table('alm_estoque')->select('material', DB::raw('MAX(id) as max_id'))->where('date_time', '<', $d->iniDate)->groupBy('material'),
                'ult', 'est.id', '=', 'ult.max_id'
            )
            ->join('mat_descricaodetalhada as dd', 'est.material', '=', 'dd.id')
            ->join('mat_descricaoresumida as dr', 'dd.descricao_resumida', '=', 'dr.id')
            ->join('mat_planocontas as pc', 'dr.ContaContabil', '=', 'pc.id')
            ->join('mat_produtos as pr', 'dr.id_produto', '=', 'pr.id')
            ->join('mat_setores as s', 'est.id_setor', '=', 's.id')
            ->join('cad_centrocusto as cc', 's.centrocusto', '=', 'cc.codigo')
            ->where('dr.id_tipo_material', '<>', 'P')
            ->when($ccFiltro, fn($q, $v) => $q->where('cc.codigo', $v))
            ->select('cc.codigo as cc_codigo', 'cc.descricao as cc_descricao', 'pc.codigo as conta_contabil', 'pr.CodigodaClasse as produto', 'pr.item_patrimonial', 'pr.DescricaodaClasse as descricao', DB::raw('SUM(est.valor_total_estoque) as sa'))
            ->groupBy('cc.codigo', 'cc.descricao', 'pc.codigo', 'pr.CodigodaClasse', 'pr.item_patrimonial', 'pr.DescricaodaClasse')
            ->get();

        $subMov = DB::connection('egap')->table('alm_estoque as est')
            ->join('mat_descricaodetalhada as dd', 'est.material', '=', 'dd.id')
            ->join('mat_descricaoresumida as dr', 'dd.descricao_resumida', '=', 'dr.id')
            ->join('mat_planocontas as pc', 'dr.ContaContabil', '=', 'pc.id')
            ->join('mat_produtos as pr', 'dr.id_produto', '=', 'pr.id')
            ->join('mat_setores as s', 'est.id_setor', '=', 's.id')
            ->join('cad_centrocusto as cc', 's.centrocusto', '=', 'cc.codigo')
            ->where('dr.id_tipo_material', '<>', 'P')
            ->when($ccFiltro, fn($q, $v) => $q->where('cc.codigo', $v))
            ->whereBetween('est.date_time', [$inicioano, $d->fim])
            ->select('cc.codigo as cc_codigo', 'cc.descricao as cc_descricao', 'pc.codigo as conta_contabil', 'pr.CodigodaClasse as produto', 'pr.item_patrimonial', 'pr.DescricaodaClasse as descricao')
            ->selectRaw("SUM(CASE WHEN tipo_movimentacao = 1 AND date_time BETWEEN ? AND ? THEN valor_total ELSE 0 END) as entradas", [$d->ini, $d->fim])
            ->selectRaw("SUM(CASE WHEN tipo_movimentacao = 2 AND date_time BETWEEN ? AND ? THEN valor_total ELSE 0 END) as saidas", [$d->ini, $d->fim])
            ->selectRaw("SUM(CASE WHEN tipo_movimentacao = 2 AND date_time >= ? AND date_time < ? THEN valor_total ELSE 0 END) as saidas_acum", [$inicioano, $d->ini])
            ->groupBy('cc.codigo', 'cc.descricao', 'pc.codigo', 'pr.CodigodaClasse', 'pr.item_patrimonial', 'pr.DescricaodaClasse')
            ->get();

        $map = [];
        foreach ($subSa as $r) {
            $key = "{$r->cc_codigo}|{$r->conta_contabil}|{$r->produto}";
            $map[$key] = (array) $r + ['entradas' => 0, 'saidas' => 0, 'saidas_acum' => 0];
        }
        foreach ($subMov as $r) {
            $key = "{$r->cc_codigo}|{$r->conta_contabil}|{$r->produto}";
            if (!isset($map[$key])) {
                $map[$key] = (array) $r + ['sa' => 0];
            }
            $map[$key]['entradas'] += $r->entradas;
            $map[$key]['saidas'] += $r->saidas;
            $map[$key]['saidas_acum'] += $r->saidas_acum;
        }

        $dados = collect(array_values($map))->map(function($i) {
            $i['saldo_atual'] = $i['sa'] + $i['entradas'] - $i['saidas'];
            return (object) $i;
        })->filter(function($i) {
            return $i->sa != 0 || $i->entradas != 0 || $i->saidas != 0 || $i->saidas_acum != 0;
        })->sortBy([
            ['cc_codigo', 'asc'],
            ['conta_contabil', 'asc'],
            ['produto', 'asc']
        ]);

        return $this->render('resumo-inventario-almoxarifado-cc', $dados, $filtros);
    }

    private function gerarPedidosBensPermanentes($filtros) 
    {
        return $this->processarRelatorioPedidosBase($filtros, 1);
    }

    private function gerarPedidosBensPermanentesValidados($filtros) 
    {
        return $this->processarRelatorioPedidosBase($filtros, 2);
    }

    private function gerarPedidosBensConsumoDuravel($filtros) 
    {
        return $this->processarRelatorioPedidosBase($filtros, 3);
    }

    private function processarRelatorioPedidosBase($filtros, $tipoRelatorio)
    {
        $d = $this->getPeriodo($filtros);

        $dados = DB::connection('egap')->table('ped_pedidos as ped')
            ->leftJoin('ped_itempedido as item', 'ped.id', '=', 'item.idPedido')
            ->leftJoin('mat_descricaoresumida as de', 'item.material', '=', 'de.id')
            ->leftJoin('mat_descricaodetalhada as dd', 'item.DescricaoDetalhada', '=', 'dd.id')
            ->leftJoin('mat_setores as unid', 'ped.UnidadeJudiciaria', '=', 'unid.id')
            ->leftJoin('mat_setores as se', 'ped.Setor', '=', 'se.id')
            ->leftJoin('ped_situacao as sit', 'item.situacao', '=', 'sit.id')
            ->where(function($q) use ($tipoRelatorio, $d) {
                if ($tipoRelatorio == 3) {
                    $q->where('ped.setor_responsavel', 799);
                } else {
                    $q->where(fn($sub) => $sub->whereNull('ped.setor_responsavel')->orWhere('ped.setor_responsavel', 1239));
                }

                $campoData = ($tipoRelatorio == 2) ? 'item.data_validacao' : 'ped.date_time';
                $q->whereBetween(DB::raw("DATE_FORMAT($campoData, '%Y-%m-%d')"), [$d->iniDate, $d->fimDate]);
            })
            ->when($filtros['unidade_judiciaria'] ?? null, function($q, $v) use ($filtros) {
                if (empty($filtros['setor_pedido'])) {
                    $q->where(fn($sq) => $sq->where('s.CodigoPai', $v)->orWhere('s.id', $v));
                }
            })
            ->when($filtros['setor_pedido'] ?? null, fn($q, $v) => $q->where('ped.Setor', $v))
            ->when($filtros['material_pedido'] ?? null, fn($q, $v) => $q->where('de.id', $v))
            ->when($filtros['situacao_pedido'] ?? null, function($q, $v) {
                $q->where('item.situacao', $v);
                if ($v == 7) {
                    $q->where(fn($sq) => $sq->whereNull('item.QuantidadeMaterialAtendida')->orWhereRaw('IFNULL(item.quantidade_validada, 0) <= item.QuantidadeMaterial'))
                      ->whereRaw('IFNULL(item.quantidade_validada, 0) <> 0');
                }
            })
            ->select(
                'ped.id as pedido_id',
                'item.id as item_id',
                'ped.date_time',
                'ped.num_protocolo',
                'unid.Setor as unidade_nome',
                'se.Setor as setor_nome',
                'de.Descricao as desc_resumida',
                'dd.descricao_detalhada as desc_detalhada',
                'item.justificativa',
                'item.QuantidadeMaterial as qtde_solicitada',
                'item.quantidade_validada',
                'item.QuantidadeMaterialAtendida as qtde_atendida',
                'ped.Observacao as obs_pedido',
                'item.ObservacaoItem as obs_item',
                'item.data_validacao',
                DB::raw('IFNULL(sit.Descricao, "Em análise") as situacao_desc')
            )
            ->orderBy('unid.Setor')
            ->orderBy('se.Setor')
            ->orderBy('de.Descricao')
            ->get()
            ->map(function($i) {
                $i->pedido_formatado = $i->pedido_id . '/' . \Carbon\Carbon::parse($i->date_time)->format('Y');
                $i->data_pedido_fmt = \Carbon\Carbon::parse($i->date_time)->format('d/m/Y');

                $qtdValidada = ($i->quantidade_validada === null || $i->quantidade_validada === '') ? $i->qtde_solicitada : $i->quantidade_validada;
                $i->qtde_validada_calc = $qtdValidada;
                $i->resta = $qtdValidada - $i->qtde_atendida;

                $i->data_validacao_fmt = ($i->data_validacao && $i->data_validacao != '0000-00-00 00:00:00') ? \Carbon\Carbon::parse($i->data_validacao)->format('d/m/Y') : '';
                return $i;
            });

        return $this->render('relatorio-pedidos', $dados, $filtros, [
            'tipoRelatorio' => $tipoRelatorio,
            'setorResponsavel' => $tipoRelatorio == 3 ? 'Seção de Materiais de Consumo' : 'Seção de Patrimônio'
        ]);
    }

    private function gerarBensSemTrValidos($filtros)
    {
        $subMaxTransfer = DB::connection('egap')->table('mat_transferencia')
            ->select('NumPatrimonio', DB::raw('MAX(id) as max_id'))
            ->groupBy('NumPatrimonio');

        $subMaxArq = DB::connection('egap')->table('mat_arquivodigital')
            ->select('termo', DB::raw('MAX(id) as max_id'))
            ->groupBy('termo');

        $dados = DB::connection('egap')->table('mat_patrimonio as pat')
            ->join('mat_descricaoresumida as dr', 'pat.DescricaoResumidadoBem', '=', 'dr.id')
            ->leftJoinSub($subMaxTransfer, 'um', 'um.NumPatrimonio', '=', 'pat.id')
            ->leftJoin('mat_transferencia as t', 't.id', '=', 'um.max_id')
            ->leftJoin('mat_setores as s', 't.SetorAtual', '=', 's.id')
            ->leftJoinSub($subMaxArq, 'ua', 'ua.termo', '=', 't.Termo')
            ->leftJoin('mat_termos as ter', 'ter.id', '=', 'ua.termo')
            ->leftJoin('mat_arquivodigital as arq', 'arq.id', '=', 'ua.max_id')
            ->whereIn('pat.SituacaoBem', [1, 7])
            ->whereNotIn('pat.id', function($query) {
                $query->select('p.id')
                    ->from('mat_patrimonio as p')
                    ->join('mat_transferencia as t', 'p.id', '=', 't.NumPatrimonio')
                    ->join('mat_arquivodigital as a', 't.Termo', '=', 'a.termo')
                    ->where('a.situacao', 1) 
                    ->whereIn('p.SituacaoBem', [1, 7]);
            })
            ->select(
                'pat.NumPatrimonio as patrimonio',
                'dr.Descricao as descricao',
                's.UnidadeOrganizacional as unidade',
                's.Setor as setor',
                'ter.num_termo',
                'ter.ano_termo',
                'arq.observacao',
                'arq.situacao as situacao_id'
            )
            ->orderBy('dr.Descricao')
            ->orderBy('pat.NumPatrimonio')
            ->get()
            ->map(function($i) {
                $i->termo_completo = ($i->num_termo && $i->ano_termo) ? $i->num_termo . '/' . $i->ano_termo : '';

                if ($i->situacao_id == 1) $i->situacao_desc = 'VALIDADO';
                elseif ($i->situacao_id == 2) $i->situacao_desc = 'INVALIDADO';
                elseif ($i->situacao_id == 3) $i->situacao_desc = 'CANCELADO';
                else $i->situacao_desc = 'PENDENTE';

                return $i;
            });

        return $this->render('bens-sem-tr-validos', $dados, $filtros);
    }
 
    private function gerarDiferencaContabil($filtros)
    {
        $totalAtivos = DB::connection('egap')->table('mat_patrimonio')
            ->whereIn('SituacaoBem', [1, 7])
            ->count();

        $dados = DB::connection('egap')->table('mat_patrimonio as pat')
            ->leftJoin('mat_situacao as sit', 'pat.SituacaoBem', '=', 'sit.id')
            ->where(function ($q) {
                $q->whereColumn('pat.ValorAquisicao', '<', 'pat.ValordaReavaliacao')
                  ->orWhere('sit.id', 8);
            })
            ->whereNotIn('sit.id', [8, 9])
            ->where('pat.ValorAquisicao', '<', 20)
            ->select(
                'sit.descricao as situacao',
                DB::raw('COUNT(*) as qtde'),
                DB::raw('SUM(pat.ValordaReavaliacao) as reavaliacao'),
                DB::raw('SUM(pat.ValorAquisicao) as aquisicao'),
                DB::raw('SUM(pat.ValordaReavaliacao - pat.ValorAquisicao) as diferenca')
            )
            ->groupBy('sit.descricao')
            ->orderBy('sit.descricao')
            ->get();

        return $this->render('diferenca-contabil', $dados, $filtros, [
            'totalAtivos' => $totalAtivos
        ]);
    }

    private function gerarEstaticoAcuraciaDocumental($filtros)
    {
        $totalAtivo = DB::connection('egap')->table('mat_patrimonio')
            ->whereIn('SituacaoBem', [1, 7])
            ->count();

        $totalValidos = DB::connection('egap')->table('mat_patrimonio as p')
            ->join('mat_transferencia as t', 'p.id', '=', 't.NumPatrimonio')
            ->join('mat_arquivodigital as a', 't.Termo', '=', 'a.termo')
            ->where('a.situacao', 1)
            ->whereIn('p.SituacaoBem', [1, 7])
            ->distinct('p.id')
            ->count('p.id');

        $totalOutros = $totalAtivo - $totalValidos;

        $percValidos = $totalAtivo > 0 ? ($totalValidos / $totalAtivo) * 100 : 0;
        $percOutros = $totalAtivo > 0 ? ($totalOutros / $totalAtivo) * 100 : 0;

        return $this->render('estatico-acuracia-documental', null, $filtros, [
            'totalAtivo'   => $totalAtivo,
            'totalValidos' => $totalValidos,
            'totalOutros'  => $totalOutros,
            'percValidos'  => $percValidos,
            'percOutros'   => $percOutros,
        ]);
    }

    private function gerarEstoqueAtual($filtros)
    {
        $subUltimoRegistro = DB::connection('egap')->table('alm_estoque')
            ->select(DB::raw('MAX(id) as max_id'), 'material')
            ->groupBy('material');

        $dados = DB::connection('egap')->table('alm_estoque as est')
            ->join('mat_descricaodetalhada as dd', 'est.material', '=', 'dd.id')
            ->join('mat_descricaoresumida as dr', 'dd.descricao_resumida', '=', 'dr.id')
            ->leftJoin('mat_unidades as u', 'dd.unidade_medida', '=', 'u.id')
            ->joinSub($subUltimoRegistro, 'ult', 'est.id', '=', 'ult.max_id')
            ->where('dr.id_tipo_material', '<>', 'P')
            ->where('dd.item_estoque', 1)
            ->select(
                'dr.id_tipo_material',
                'dd.descricao_detalhada',
                'u.Sigla as sigla',
                'est.quantidade_estoque',
                'est.preco_medio_estoque',
                'est.valor_total_estoque',
                'est.date_time as atualizado_em'
            )
            ->orderBy('dr.id_tipo_material')
            ->orderBy('dd.descricao_detalhada')
            ->get()
            ->map(function($item) {
                $item->grupo_desc = ($item->id_tipo_material == 'C') ? 'Materiais de Consumo' : 'Materiais de Consumo Duráveis';
                $item->data_formatada = $item->atualizado_em ? \Carbon\Carbon::parse($item->atualizado_em)->format('d/m/Y') : '';
                return $item;
            });

        $dadosAgrupados = $dados->groupBy('grupo_desc');

        return $this->render('estoque-atual', null, $filtros, [
            'dadosAgrupados' => $dadosAgrupados,
            'totalGeral'     => $dados->sum('valor_total_estoque')
        ]);
    }

    private function gerarQtdMaterialSetor($filtros)
    {
        $dados = DB::connection('egap')->table('ped_pedidos as ped')
            ->join('ped_itempedido as iped', 'ped.id', '=', 'iped.idPedido')
            ->join('mat_descricaodetalhada as dd', 'iped.DescricaoDetalhada', '=', 'dd.id')
            ->join('mat_setores as se', 'ped.Setor', '=', 'se.id')
            ->where('ped.setor_responsavel', 799)
            ->select(
                'se.UnidadeOrganizacional', 
                'se.Setor', 
                'dd.descricao_detalhada',
                DB::raw('SUM(iped.QuantidadeMaterial) as solicitado'),
                DB::raw('SUM(iped.QuantidadeMaterialAtendida) as atendido')
            )
            ->groupBy('se.UnidadeOrganizacional', 'se.Setor', 'dd.descricao_detalhada')
            ->orderBy('se.UnidadeOrganizacional')
            ->orderBy('se.Setor')
            ->orderBy('dd.descricao_detalhada')
            ->get();

        $dadosAgrupados = $dados->groupBy('UnidadeOrganizacional');

        return $this->render('qtd-material-setor', null, $filtros, [
            'dadosAgrupados' => $dadosAgrupados
        ]);
    }

    private function gerarQtdInsumosImpressao($filtros)
    {
        $temDatas = !empty($filtros['data_inicio']) && !empty($filtros['data_termino']);

        if (!$temDatas) {
            return $this->render('qtd-insumos-impressao', collect(), $filtros, [
                'mostrarDados' => false
            ]);
        }

        $d = $this->getPeriodo($filtros);

        $subPreco = DB::connection('egap')->table('alm_estoque')
            ->select('material as id_material', DB::raw('MAX(preco_unitario) as valor'))
            ->groupBy('material');

        $dados = DB::connection('egap')->table('ped_pedidos as ped')
            ->join('ped_itempedido as iped', 'ped.id', '=', 'iped.idPedido')
            ->join('mat_descricaodetalhada as dd', 'iped.DescricaoDetalhada', '=', 'dd.id')
            ->join('mat_setores as se', 'ped.Setor', '=', 'se.id')
            ->join('mat_setores as unid', 'se.CodigodaUO', '=', 'unid.id')
            ->leftJoinSub($subPreco, 'compra', 'dd.id', '=', 'compra.id_material')
            ->where('ped.setor_responsavel', 799)
            ->where(function($q) {
                $q->whereIn('dd.descricao_resumida', [400, 425])
                  ->orWhere('dd.descricao_detalhada', 'LIKE', '%PAPEL A4%');
            })
            ->whereBetween('ped.date_time', [$d->ini, $d->fim])
            ->select(
                'se.CodigodaUO',
                'unid.UnidadeOrganizacional',
                'se.Setor',
                'dd.id as material_id',
                'dd.descricao_detalhada',
                DB::raw('IFNULL(compra.valor, 0) as valor_unitario'),
                DB::raw('SUM(iped.QuantidadeMaterialAtendida) as atendido')
            )
            ->groupBy('se.CodigodaUO', 'unid.UnidadeOrganizacional', 'se.Setor', 'dd.id', 'dd.descricao_detalhada', 'compra.valor')
            ->havingRaw('SUM(iped.QuantidadeMaterialAtendida) > 0')
            ->orderBy('unid.UnidadeOrganizacional')
            ->get()
            ->map(function($i) {
                $i->total = $i->atendido * $i->valor_unitario;
                return $i;
            });

        $porUO = $dados->groupBy('UnidadeOrganizacional');

        $resumoGeralMaterial = $dados->groupBy('descricao_detalhada')->map(function($itens, $desc) {
            return (object)[
                'descricao' => $desc,
                'atendido' => $itens->sum('atendido'),
                'valor_unitario' => $itens->first()->valor_unitario,
                'total' => $itens->sum('total')
            ];
        })->sortBy('descricao')->values();

        $resumoGeralUnidade = $dados->groupBy('UnidadeOrganizacional')->map(function($itens, $desc) {
            return (object)[
                'descricao' => $desc,
                'total' => $itens->sum('total')
            ];
        })->sortBy('descricao')->values();

        return $this->render('qtd-insumos-impressao', null, $filtros, [
            'mostrarDados' => true,
            'porUO' => $porUO,
            'resumoGeralMaterial' => $resumoGeralMaterial,
            'resumoGeralUnidade' => $resumoGeralUnidade,
            'periodoStr' => $d->iniDate != $d->fimDate 
                ? $d->objIni->format('d/m/Y') . ' a ' . $d->objFim->format('d/m/Y') 
                : $d->objIni->format('d/m/Y')
        ]);
    }

    private function gerarQtdMaterialConsumoUnidade($filtros)
    {
        $temDatas = !empty($filtros['data_inicio']) && !empty($filtros['data_termino']);

        $setoresRaw = DB::connection('egap')->table('mat_setores')
            ->select('id', 'Setor', 'CodigodaUO')
            ->orderBy('CodigodaUO')->orderBy('Setor')
            ->get();
            
        $listaUnidades = [];
        foreach ($setoresRaw as $s) {
            $tipo = ($s->id == $s->CodigodaUO) ? 'U' : 'S';
            $listaUnidades[$s->id . '|' . $tipo] = $s->Setor;
        }

        if (!$temDatas) {
            return $this->render('qtd-material-consumo-unidade', collect(), $filtros, [
                'mostrarDados' => false,
                'listaUnidades' => $listaUnidades
            ]);
        }

        $d = $this->getPeriodo($filtros);
        
        $unidadeSelecionada = $filtros['unidade_selecionada'] ?? null;
        $unidDescricao = '';

        $subPreco = DB::connection('egap')->table('alm_estoque')
            ->select('material as id_material', DB::raw('MAX(preco_unitario) as valor'))
            ->groupBy('material');

        $dados = DB::connection('egap')->table('ped_pedidos as ped')
            ->join('ped_itempedido as iped', 'ped.id', '=', 'iped.idPedido')
            ->join('mat_descricaodetalhada as dd', 'iped.DescricaoDetalhada', '=', 'dd.id')
            ->join('mat_setores as se', 'ped.Setor', '=', 'se.id')
            ->join('mat_setores as unid', 'se.CodigodaUO', '=', 'unid.id')
            ->leftJoinSub($subPreco, 'compra', 'dd.id', '=', 'compra.id_material')
            ->where('ped.setor_responsavel', 799)
            ->whereBetween('ped.date_time', [$d->ini, $d->fim])
            ->when($unidadeSelecionada, function($q, $v) use (&$unidDescricao, $listaUnidades) {
                $parts = explode('|', $v);
                if (count($parts) == 2) {
                    $id = $parts[0];
                    $tipo = $parts[1];
                    
                    if ($tipo == 'U') {
                        $q->where('se.CodigodaUO', $id);
                    } else {
                        $q->where('se.id', $id);
                    }
                    
                    $unidDescricao = $listaUnidades[$v] ?? '';
                }
            })
            ->select(
                'se.CodigodaUO',
                'unid.UnidadeOrganizacional',
                'se.Setor',
                'dd.id as material_id',
                'dd.descricao_detalhada',
                DB::raw('IFNULL(compra.valor, 0) as valor_unitario'),
                DB::raw('SUM(iped.QuantidadeMaterialAtendida) as atendido')
            )
            ->groupBy('se.CodigodaUO', 'unid.UnidadeOrganizacional', 'se.Setor', 'dd.id', 'dd.descricao_detalhada', 'compra.valor')
            ->havingRaw('SUM(iped.QuantidadeMaterialAtendida) > 0')
            ->orderBy('unid.UnidadeOrganizacional')
            ->get()
            ->map(function($i) {
                $i->total = $i->atendido * $i->valor_unitario;
                return $i;
            });

        $porUO = $dados->groupBy('UnidadeOrganizacional');

        $resumoGeralMaterial = $dados->groupBy('descricao_detalhada')->map(function($itens, $desc) {
            return (object)[
                'descricao' => $desc,
                'atendido' => $itens->sum('atendido'),
                'valor_unitario' => $itens->first()->valor_unitario,
                'total' => $itens->sum('total')
            ];
        })->sortBy('descricao')->values();

        $resumoGeralUnidade = $dados->groupBy('UnidadeOrganizacional')->map(function($itens, $desc) {
            return (object)[
                'descricao' => $desc,
                'total' => $itens->sum('total')
            ];
        })->sortBy('descricao')->values();

        return $this->render('qtd-material-consumo-unidade', null, $filtros, [
            'mostrarDados' => true,
            'listaUnidades' => $listaUnidades,
            'unidDescricao' => $unidDescricao,
            'porUO' => $porUO,
            'resumoGeralMaterial' => $resumoGeralMaterial,
            'resumoGeralUnidade' => $resumoGeralUnidade,
            'periodoStr' => $d->iniDate != $d->fimDate 
                ? $d->objIni->format('d/m/Y') . ' a ' . $d->objFim->format('d/m/Y') 
                : $d->objIni->format('d/m/Y')
        ]);
    }

    private function gerarAquisicaoMateriaisComarca($filtros)
    {
        $temDatas = !empty($filtros['data_inicio']) && !empty($filtros['data_termino']);

        if (!$temDatas) {
            return $this->render('aquisicao-materiais-comarca', collect(), $filtros, [
                'mostrarDados' => false,
                'data_inicio_padrao' => now()->format('Y-m-d'),
                'data_termino_padrao' => now()->format('Y-m-d')
            ]);
        }

        $d = $this->getPeriodo($filtros);

        $materiaisIds = $filtros['materiais'] ?? [];

        $dados = DB::connection('egap')->table('alm_notafiscal as nf')
            ->join('alm_itens_notafiscal as inf', 'nf.id', '=', 'inf.id_notafiscal')
            ->leftJoin('mat_descricaodetalhada as dd', 'inf.id_material', '=', 'dd.id')
            ->where('nf.unidade_judiciaria', 766)
            ->where('nf.situacao', 3)
            ->whereBetween('nf.data_documento', [$d->ini, $d->fim])
            ->when(!empty($materiaisIds), function($q) use ($materiaisIds) {
                $q->whereIn('dd.id', $materiaisIds);
            })
            ->select(
                DB::raw("DATE_FORMAT(nf.data_documento, '%Y%m') as mesano"),
                'dd.id as id_descricao_detalhada',
                'dd.descricao_detalhada',
                DB::raw('SUM(ROUND(inf.quantidade, 0)) as quantidade'),
                DB::raw('SUM(inf.preco_unitario) as preco_unitario'),
                DB::raw('SUM(inf.total_item) as valor_total')
            )
            ->groupBy(DB::raw("DATE_FORMAT(nf.data_documento, '%Y%m')"), 'dd.id', 'dd.descricao_detalhada')
            ->orderBy(DB::raw("DATE_FORMAT(nf.data_documento, '%Y%m')"))
            ->orderBy('dd.descricao_detalhada')
            ->get();

        $mesesNome = ['', 'Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'];
        
        $dadosAgrupados = $dados->groupBy('mesano')->map(function($itens, $mesano) use ($mesesNome) {
            $ano = substr($mesano, 0, 4);
            $mes = (int) substr($mesano, -2);
            $nomeAgrupamento = $mesesNome[$mes] . '/' . $ano;
            
            return (object) [
                'nome' => $nomeAgrupamento,
                'itens' => $itens
            ];
        });

        return $this->render('aquisicao-materiais-comarca', null, $filtros, [
            'mostrarDados' => true,
            'dadosAgrupados' => $dadosAgrupados,
            'data_inicio_padrao' => $d->iniDate,
            'data_termino_padrao' => $d->fimDate,
            'periodoStr' => \Carbon\Carbon::parse($d->iniDate)->format('d/m/Y') . ' a ' . \Carbon\Carbon::parse($d->fimDate)->format('d/m/Y')
        ]);
    }

    private function gerarAquisicaoMateriaisEstoqueVisivel($filtros)
    {
        $temDatas = !empty($filtros['data_inicio']) && !empty($filtros['data_termino']);

        if (!$temDatas) {
            return $this->render('aquisicao-materiais-estoque-visivel', collect(), $filtros, [
                'mostrarDados' => false,
                'data_inicio_padrao' => now()->format('Y-m-d'),
                'data_termino_padrao' => now()->format('Y-m-d')
            ]);
        }

        $d = $this->getPeriodo($filtros);

        $materiaisIds = $filtros['materiais'] ?? [];

        $dados = DB::connection('egap')->table('alm_notafiscal as nf')
            ->join('alm_itens_notafiscal as inf', 'nf.id', '=', 'inf.id_notafiscal')
            ->leftJoin('mat_descricaodetalhada as dd', 'inf.id_material', '=', 'dd.id')
            ->leftJoin('mat_descricaoresumida as dr', 'dd.descricao_resumida', '=', 'dr.id')
            ->where('nf.unidade_judiciaria', 766)
            ->where('nf.situacao', 3)
            ->where(function($q) {
                $q->where('dd.item_estoque', 1)
                  ->orWhere('dd.visibilidade', '<>', 0)
                  ->orWhereNotNull('dd.visibilidade');
            })
            ->whereBetween('nf.data_documento', [$d->ini, $d->fim])
            ->when(!empty($materiaisIds), function($q) use ($materiaisIds) {
                $q->whereIn('dd.id', $materiaisIds);
            })
            ->select(
                DB::raw("DATE_FORMAT(nf.data_documento, '%Y%m') as mesano"),
                'dd.id as id_descricao_detalhada',
                'dd.descricao_detalhada',
                DB::raw('SUM(ROUND(inf.quantidade, 0)) as quantidade'),
                DB::raw('SUM(inf.preco_unitario) as preco_unitario'),
                DB::raw('SUM(inf.total_item) as valor_total')
            )
            ->groupBy(DB::raw("DATE_FORMAT(nf.data_documento, '%Y%m')"), 'dd.id', 'dd.descricao_detalhada')
            ->orderBy(DB::raw("DATE_FORMAT(nf.data_documento, '%Y%m')"))
            ->orderBy('dd.descricao_detalhada')
            ->get();

        $mesesNome = ['', 'Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'];
        
        $dadosAgrupados = $dados->groupBy('mesano')->map(function($itens, $mesano) use ($mesesNome) {
            $ano = substr($mesano, 0, 4);
            $mes = (int) substr($mesano, -2);
            $nomeAgrupamento = $mesesNome[$mes] . '/' . $ano;
            
            return (object) [
                'nome' => $nomeAgrupamento,
                'itens' => $itens
            ];
        });

        return $this->render('aquisicao-materiais-estoque-visivel', null, $filtros, [
            'mostrarDados' => true,
            'dadosAgrupados' => $dadosAgrupados,
            'data_inicio_padrao' => $d->iniDate,
            'data_termino_padrao' => $d->fimDate,
            'periodoStr' => \Carbon\Carbon::parse($d->iniDate)->format('d/m/Y') . ' a ' . \Carbon\Carbon::parse($d->fimDate)->format('d/m/Y')
        ]);
    }
    
    private function gerarEstatisticoConsumoAlmoxarifadoMeta($filtros)
    {
        $ano = $filtros['ano'] ?? null;

        $setoresRaw = DB::connection('egap')->table('mat_setores')->orderBy('CodigodaUO')->orderBy('Setor')->get();
        $listaSetores = [];
        $arrSetores = [];

        foreach ($setoresRaw as $s) {
            $unid = ($s->id == $s->CodigodaUO) ? 'U' : 'S';
            $listaSetores[] = (object) ['val' => "{$s->id}|{$unid}", 'label' => $s->Setor];
            $arrSetores[$s->id] = $s->Setor;
        }

        if (!$ano) {
            return $this->render('estatistico-consumo-almoxarifado-meta', collect(), $filtros, [
                'mostrarDados' => false,
                'ano_padrao' => date('Y'),
                'listaSetores' => $listaSetores
            ]);
        }

        $anoAnt = $ano - 1;
        $unidadeSelecionada = $filtros['unidade'] ?? '-1|T';
        $apenasForaMeta = isset($filtros['meta']) && $filtros['meta'] == 'S';
        
        $tipoFiltro = '';
        $codigoFiltro = '';
        $comarcas = [];

        $partes = explode('|', $unidadeSelecionada);
        if (count($partes) == 2) {
            $codigoFiltro = $partes[0];
            $tipoFiltro = $partes[1];

            if ($tipoFiltro == 'T') {
                $comarcas = [766 => 'TRIBUNAL DE JUSTIÇA', 866 => 'CORREGEDORIA'];
            } elseif ($tipoFiltro == 'C') {
                foreach ($setoresRaw as $s) {
                    if ($s->id == $s->CodigodaUO && $s->id != 766 && $s->id != 866) {
                        $nome = ($s->Setor == 'COMARCA DA CAPITAL - JUÍZO DE VITÓRIA') ? 'COMARCA DE VITÓRIA' : $s->Setor;
                        $comarcas[$s->id] = $nome;
                    }
                }
            } else {
                $comarcas[$codigoFiltro] = $arrSetores[$codigoFiltro] ?? 'SETOR DESCONHECIDO';
            }
        }
        asort($comarcas);

        $relatorioFinal = [];

        foreach ($comarcas as $cod => $nomeUnidade) {
            $query = DB::connection('egap')->table('mat_descricaodetalhada as dd')
                ->leftJoin('ped_itempedido as iped', 'dd.id', '=', 'iped.DescricaoDetalhada')
                ->leftJoin('ped_pedidos as ped', function($join) use ($ano, $anoAnt) {
                    $join->on('iped.idPedido', '=', 'ped.id')
                         ->where('ped.setor_responsavel', 799)
                         ->whereIn(DB::raw('YEAR(ped.date_time)'), [$ano, $anoAnt]);
                })
                ->leftJoin('mat_setores as se', 'ped.Setor', '=', 'se.id')
                ->where('dd.item_estoque', 1)
                ->where(function($q) use ($tipoFiltro, $cod) {
                    $q->whereNull('ped.id');
                    if ($tipoFiltro == 'S') {
                        $q->orWhere('se.id', $cod);
                    } else {
                        $q->orWhere('se.CodigodaUO', $cod);
                    }
                });

            $dados = $query->select(
                'dd.id', 'dd.descricao_detalhada',
                DB::raw("SUM(CASE WHEN YEAR(ped.date_time) = {$anoAnt} AND MONTH(ped.date_time) IN (1,2,3) THEN iped.QuantidadeMaterialAtendida ELSE 0 END) as ant_q1"),
                DB::raw("SUM(CASE WHEN YEAR(ped.date_time) = {$anoAnt} AND MONTH(ped.date_time) IN (4,5,6) THEN iped.QuantidadeMaterialAtendida ELSE 0 END) as ant_q2"),
                DB::raw("SUM(CASE WHEN YEAR(ped.date_time) = {$anoAnt} AND MONTH(ped.date_time) IN (7,8,9) THEN iped.QuantidadeMaterialAtendida ELSE 0 END) as ant_q3"),
                DB::raw("SUM(CASE WHEN YEAR(ped.date_time) = {$anoAnt} AND MONTH(ped.date_time) IN (10,11,12) THEN iped.QuantidadeMaterialAtendida ELSE 0 END) as ant_q4"),
                DB::raw("SUM(CASE WHEN YEAR(ped.date_time) = {$ano} AND MONTH(ped.date_time) IN (1,2,3) THEN iped.QuantidadeMaterialAtendida ELSE 0 END) as atu_q1"),
                DB::raw("SUM(CASE WHEN YEAR(ped.date_time) = {$ano} AND MONTH(ped.date_time) IN (4,5,6) THEN iped.QuantidadeMaterialAtendida ELSE 0 END) as atu_q2"),
                DB::raw("SUM(CASE WHEN YEAR(ped.date_time) = {$ano} AND MONTH(ped.date_time) IN (7,8,9) THEN iped.QuantidadeMaterialAtendida ELSE 0 END) as atu_q3"),
                DB::raw("SUM(CASE WHEN YEAR(ped.date_time) = {$ano} AND MONTH(ped.date_time) IN (10,11,12) THEN iped.QuantidadeMaterialAtendida ELSE 0 END) as atu_q4")
            )
            ->groupBy('dd.id', 'dd.descricao_detalhada')
            ->orderBy('dd.descricao_detalhada')
            ->get();

            $linhas = [];
            foreach ($dados as $dado) {
                $linha = (object) [
                    'descricao' => $dado->descricao_detalhada,
                    'ant_q1' => $dado->ant_q1, 'ant_q2' => $dado->ant_q2, 'ant_q3' => $dado->ant_q3, 'ant_q4' => $dado->ant_q4,
                    'ant_total' => $dado->ant_q1 + $dado->ant_q2 + $dado->ant_q3 + $dado->ant_q4,
                    'atu_q1' => $dado->atu_q1, 'atu_q2' => $dado->atu_q2, 'atu_q3' => $dado->atu_q3, 'atu_q4' => $dado->atu_q4,
                    'atu_total' => $dado->atu_q1 + $dado->atu_q2 + $dado->atu_q3 + $dado->atu_q4,
                ];

                $teveForaMeta = false;

                $calc = function($ant, $atu) use (&$teveForaMeta) {
                    $metaQtd = $ant - ($ant * 0.25);
                    $foraDaMeta = ($metaQtd < $atu);
                    if ($foraDaMeta) $teveForaMeta = true;

                    $percent = ($ant == 0) ? ($atu * 100) : (($atu * 100 / $ant) - 100);
                    return ['val' => $atu, 'fora' => $foraDaMeta, 'perc' => $percent];
                };

                $linha->res_q1 = $calc($linha->ant_q1, $linha->atu_q1);
                $linha->res_q2 = $calc($linha->ant_q2, $linha->atu_q2);
                $linha->res_q3 = $calc($linha->ant_q3, $linha->atu_q3);
                $linha->res_q4 = $calc($linha->ant_q4, $linha->atu_q4);
                $linha->res_total = $calc($linha->ant_total, $linha->atu_total);

                if ($apenasForaMeta && !$teveForaMeta) {
                    continue;
                }

                $linhas[] = $linha;
            }

            if (!empty($linhas)) {
                $relatorioFinal[] = (object) ['unidade' => $nomeUnidade, 'itens' => $linhas];
            }
        }

        $dadosView = [
            'mostrarDados' => true,
            'ano_padrao' => $ano,
            'ano' => $ano,
            'anoAnt' => $anoAnt,
            'listaSetores' => $listaSetores,
            'relatorioFinal' => $relatorioFinal
        ];

        if (isset($filtros['excel']) && $filtros['excel'] == 'S') {
            $filename = "relatorio_meta_069_" . date('Ymd') . ".xls";
            $payload = array_merge(['dados' => null, 'filtros' => $filtros, 'data_emissao' => now()->format('d/m/Y')], $dadosView);
            
            return response()->view('reports.egap.estatistico-consumo-almoxarifado-meta', $payload)
                ->header('Content-Type', 'application/vnd.ms-excel; charset=utf-8')
                ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
        }

        return $this->render('estatistico-consumo-almoxarifado-meta', null, $filtros, $dadosView);
    }

}