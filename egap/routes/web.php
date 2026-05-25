<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/egap');
});

Route::get('/patrimonio/bens-selecionados/{ids}/imprimir', function ($ids) {
    $itemIds = explode(',', $ids);
    $bens = \App\Models\Patrimonio\BensMoveis\BemMovel::with([
        'marcaRef', 'modeloRef', 'unidadeJudiciariaRef', 'setorRef', 'situacaoBemRef'
    ])->whereIn('id', $itemIds)->get();

    return view('patrimonio.relatorio-bens-lote', [
        'bens' => $bens
    ]);
})->name('bens.imprimir.lote');

Route::get('/termos/imprimir/{id}', function ($id) {
    $termo = \App\Models\Patrimonio\BensMoveis\Termo::findOrFail($id);
    $arquivoDigital = \Illuminate\Support\Facades\DB::connection('egap')->table('mat_arquivodigital')->where('termo', $id)->first();

    $termoData = \Illuminate\Support\Facades\DB::connection('egap')
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

    // 2. Consulta os Bens calculando a regra de 2015 do legado
    $bens = \Illuminate\Support\Facades\DB::connection('egap')
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
            \Illuminate\Support\Facades\DB::raw("IF(p.DatadeIncorporacao < '2015-01-01 00:00:00', p.ValordaReavaliacao, p.ValorAquisicao) as ValorCalculado")
        )->get();

    // 3. Fallbacks e Formatação do CPF
    $usuarioEmitente = $termoData->emitido_por ?? auth()->user()->name ?? 'NÃO INFORMADO';
    $cargoEmitente = $termoData->cargo ?? auth()->user()->cargo ?? 'SERVIDOR';
    $cpfRaw = $termoData->cpf ?? auth()->user()->cpf ?? null;

    $cpfEmitente = '';
    if ($cpfRaw) {
        $nbr_cpf = str_pad(preg_replace('/[^0-9]/', '', $cpfRaw), 11, '0', STR_PAD_LEFT);
        $cpfEmitente = substr($nbr_cpf, 0, 3) . '.' . substr($nbr_cpf, 3, 3) . '.' . substr($nbr_cpf, 6, 3) . '-' . substr($nbr_cpf, 9, 2);
    }

    return view('patrimonio.termo_impresso', [
        'termo' => $termo,
        'arquivoDigital' => $arquivoDigital,
        'bens' => $bens,
        'unidade' => $termoData->UnidadeJudiciaria ?? 'TRIBUNAL DE JUSTIÇA DO ESPÍRITO SANTO',
        'setor' => $termoData->Setor ?? 'NÃO INFORMADO',
        'complemento' => $termoData->ComplementoSetor ?? 'NÃO INFORMADO',
        'usuarioEmitente' => $usuarioEmitente,
        'cargoEmitente' => $cargoEmitente,
        'cpfEmitente' => $cpfEmitente
    ]);
})->name('termo.imprimir');
