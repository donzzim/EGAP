<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PrintsControllers\AgendamentoMateriaisPrintController;
use App\Http\Controllers\PrintsControllers\DepreciacaoPrintController;
use App\Http\Controllers\PrintsControllers\BaixaPrintController;
use App\Http\Controllers\PrintsControllers\PedidosPrintController;
use App\Http\Controllers\PrintsControllers\TermoInventarioPrintController;
use App\Http\Controllers\PrintsControllers\TermosPrintController;
use App\Models\Patrimonio\BensMoveis\BemMovel;


Route::get('/', function () {
    return redirect('/egap');
});

Route::prefix('egap')->group(function () {
    Route::get('/pedidos/{record}/imprimir', [PedidosPrintController::class, 'show'])->name('impressao_pedido');

    Route::group(['prefix' => 'agendamento'], function () {
        Route::get('/transporte/{id}/materiais/imprimir', [AgendamentoMateriaisPrintController::class, 'print'])
            ->name('agendamento.materiais.imprimir');
    });

    Route::group(['prefix' => 'patrimonio'], function () {
        Route::get('/baixa/{id}/imprimir', [BaixaPrintController::class, 'print'])
            ->name('termo.baixa.imprimir');

        Route::get('/depreciacao/{id}/imprimir', [DepreciacaoPrintController::class, 'print'])
            ->name('depreciacao.imprimir');

        Route::get('/bens-moveis/termos/{id}/imprimir', [TermosPrintController::class, 'print'])
            ->name('termo.imprimir');

        Route::get('/inventario/termos/{id}/imprimir', [TermoInventarioPrintController::class, 'print'])
            ->name('termo-inventario.imprimir');

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
