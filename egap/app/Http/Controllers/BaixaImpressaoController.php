<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Patrimonio\BensMoveis\Baixa;
use App\Models\Patrimonio\BensMoveis\ItemBaixa;
use Illuminate\Support\Facades\DB;

class BaixaImpressaoController extends Controller
{
    public function imprimir($id)
    {
        $baixa = Baixa::where('id', $id)
            ->first();

        if (!$baixa) {
            abort(404, 'Processo de baixa não encontrado.');
        }

        $numeroProcesso = $baixa->NumeroProcesso ?? $baixa->numero_processo ?? 'NÃO INFORMADO';

        $itens =ItemBaixa::join('mat_patrimonio', 'mat_itembaixa.id_bem', '=', 'mat_patrimonio.id')
            ->where('mat_itembaixa.id_baixa', $id)
            ->select([
                'mat_patrimonio.NumPatrimonio',
                'mat_patrimonio.Descricao as MaterialDescricao',
                'mat_patrimonio.ValorAquisicao',
                'mat_patrimonio.ValordaReavaliacao',
                'mat_patrimonio.NumerodeSerie',
            ])
            ->get();

        $totalAquisicao = $itens->sum('ValorAquisicao');
        $totalReavaliado = $itens->sum('ValordaReavaliacao');

        return view('patrimonio.impressao-baixa', [
            'baixa'          => $baixa,
            'numeroProcesso' => $numeroProcesso,
            'itens'          => $itens,
            'totalAquisicao' => $totalAquisicao,
            'totalReavaliado'=> $totalReavaliado
        ]);
    }
}
