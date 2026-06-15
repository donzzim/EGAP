<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DepreciacaoController;
use App\Http\Controllers\BaixaImpressaoController;
use App\Http\Controllers\PedidosPrintController;
use App\Http\Controllers\TermosPrintController;
use App\Models\Patrimonio\BensMoveis\BemMovel;


Route::get('/', function () {
    return redirect('/egap');
});

Route::prefix('egap')->group(function () {
    Route::get('/pedidos/{record}/imprimir', [PedidosPrintController::class, 'show'])->name('impressao_pedido');

    Route::group(['prefix' => 'patrimonio'], function () {
        Route::get('/baixa/{id}/imprimir', [BaixaImpressaoController::class, 'imprimir'])
            ->name('termo.baixa.imprimir');

        Route::get('/depreciacao/{id}/imprimir', [DepreciacaoController::class, 'imprimir'])
            ->name('depreciacao.imprimir');

        Route::get('/bens-moveis/termos/{id}/print', [TermosPrintController::class, 'print'])
            ->name('termo.imprimir');

        Route::get('/bens-selecionados/{ids}/imprimir', function ($ids) {
            $itemIds = explode(',', $ids);
            $bens = BemMovel::with([
                'marcaRef', 'modeloRef', 'unidadeJudiciariaRef', 'setorRef', 'situacaoBemRef'
            ])->whereIn('id', $itemIds)->get();

            return view('patrimonio.relatorio-bens-lote', [
                'bens' => $bens
            ]);
        })
            ->name('bens.imprimir.lote');
    });
});
