<?php

namespace App\Http\Controllers;

use App\Models\Almoxarifado\Pedidos;

class PedidosPrintController extends Controller
{
    public function show(Pedidos $record)
    {
        $pedido = $record->load([
            'solicitante_get',
            'responsavel_atendimento',
            'setor_get',
            'complementoSetor.descricao',
            'itens.materialRel',
            'itens.descricaoDetalhadaRel',
        ]);

        return view('pedidos.pedidos_impressao', compact('pedido'));
    }
}
