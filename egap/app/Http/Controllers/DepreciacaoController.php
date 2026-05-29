<?php

namespace App\Http\Controllers;

use App\Models\Patrimonio\BensMoveis\BemMovel;
use Illuminate\Support\Carbon;

class DepreciacaoController extends Controller
{
    public function imprimir(int $id)
    {
        $record = BemMovel::with([
            'contaContabilRef',
            'elementoDespesaRef',
        ])->findOrFail($id);

        $baseValue      = (float) ($record->Valor ?: $record->ValorAquisicao);
        $residual       = (float) $record->ValorResidual;
        $lifeMonths     = (int)   $record->VidaUtil ?: 1;
        $startDate      = $record->DataDisponibilizacao
            ? Carbon::parse($record->DataDisponibilizacao)
            : Carbon::parse($record->DataCadastro);

        $depreciableAmount = $baseValue - $residual;
        $monthlyDeprec     = $lifeMonths > 0 ? $depreciableAmount / $lifeMonths : 0;
        $monthsPassed      = min($startDate->diffInMonths(now()), $lifeMonths);

        $data        = [];
        $accumulated = 0;

        for ($i = 1; $i <= $monthsPassed; $i++) {
            $accumulated += $monthlyDeprec;
            $data[] = [
                'mes'       => $i,
                'data'      => $startDate->copy()->addMonths($i)->format('d/m/Y'),
                'mensal'    => $monthlyDeprec,
                'acumulada' => $accumulated,
                'liquido'   => max($baseValue - $accumulated, $residual),
            ];
        }

        return view('patrimonio.relatorio-depreciacao-print', [
            'record'       => $record,
            'dados'        => array_reverse($data),
            'vidaUtil'     => $lifeMonths,
            'valorResidual'=> $residual,
        ]);
    }
}
