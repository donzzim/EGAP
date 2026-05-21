<?php

namespace App\Http\Controllers;

use App\Models\Patrimonio\BensMoveis\Termo;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class TermosPrintController extends Controller
{
    /**
     * ✅ MÉTODO: imprimir
     * Este nome deve ser exatamente igual ao que está no seu routes/web.php
     */
    public function imprimir($id)
    {
        // 1. Busca o Termo principal ou falha se não existir
        $termo = Termo::findOrFail($id);

        // 2. Busca dados de situação do anexo (tabela mat_arquivodigital)
        $arquivoDigital = DB::connection('egap')
            ->table('mat_arquivodigital')
            ->where('termo', $id)
            ->first();

        // 3. Busca dados de localização e emitente (Baseado na última transferência)
        $termoData = DB::connection('egap')
            ->table('mat_transferencia as t')
            ->leftJoin('mat_setores as s', 't.SetorAtual', '=', 's.id')
            ->leftJoin('mat_complementosetor as c', 't.ComplementoAtual', '=', 'c.id')
            ->join('jos_users as u', 't.Usuario', '=', 'u.id')
            ->leftJoin('mat_infousers as info', 'info.usuario_id', '=', 'u.id')
            ->where('t.Termo', $id)
            ->select(
                's.UnidadeOrganizacional as UnidadeJudiciaria',
                's.Setor',
                'c.descricao as ComplementoSetor',
                'u.name as emitido_por',
                'info.cargo',
                'info.cpf'
            )->first();

        // 4. Busca os Bens com a regra de valor de 2015 que o seu Blade pede
        $bens = DB::connection('egap')
            ->table('mat_patrimonio as p')
            ->join('mat_transferencia as t', 'p.id', '=', 't.NumPatrimonio')
            ->leftJoin('mat_marca as ma', 'p.Marca', '=', 'ma.id')
            ->leftJoin('mat_modelo as mo', 'p.Modelo', '=', 'mo.id')
            ->where('t.Termo', $id)
            ->select(
                'p.NumPatrimonio',
                'p.Descricao',
                'ma.Descricao as marca_desc',
                'mo.descricao as modelo_desc',
                'p.EstadodeConservacao',
                'p.ValorAquisicao',
                'p.ValordaReavaliacao',
                'p.DatadeIncorporacao',
                // Criamos o campo ValorCalculado para o Blade não dar erro
                DB::raw("IF(p.DatadeIncorporacao < '2015-01-01 00:00:00', p.ValordaReavaliacao, p.ValorAquisicao) as ValorCalculado")
            )->get();

        // 5. Formatação do CPF do Emitente
        $cpfEmitente = '';
        if (isset($termoData->cpf)) {
            $nbr_cpf = str_pad(preg_replace('/[^0-9]/', '', $termoData->cpf), 11, '0', STR_PAD_LEFT);
            $cpfEmitente = substr($nbr_cpf, 0, 3) . '.' . substr($nbr_cpf, 3, 3) . '.' . substr($nbr_cpf, 6, 3) . '-' . substr($nbr_cpf, 9, 2);
        }

        // 6. Retorna a View com as variáveis que o seu Blade original utiliza
        return view('patrimonio.termo_impresso', [
            'termo' => $termo,
            'arquivoDigital' => $arquivoDigital,
            'bens' => $bens,
            'unidade' => $termoData->UnidadeJudiciaria ?? 'TRIBUNAL DE JUSTIÇA DO ESPÍRITO SANTO',
            'setor' => $termoData->Setor ?? 'NÃO INFORMADO',
            'complemento' => $termoData->ComplementoSetor ?? 'NÃO INFORMADO',
            'usuarioEmitente' => $termoData->emitido_por ?? Auth::user()->name ?? 'NÃO INFORMADO',
            'cargoEmitente' => $termoData->cargo ?? 'SERVIDOR',
            'cpfEmitente' => $cpfEmitente
        ]);
    }
}
