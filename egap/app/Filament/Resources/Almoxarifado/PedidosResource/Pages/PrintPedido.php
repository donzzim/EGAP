<?php

namespace App\Filament\Egap\Resources\Almoxarifado\PedidosResource\Pages;

use App\Filament\Egap\Resources\Almoxarifado\PedidosResource;
use Filament\Resources\Pages\Page;
use App\Models\Egap\Almoxarifado\Pedidos;

class PrintPedido extends Page
{
    protected static string $resource = PedidosResource::class;

//    protected string $view = 'egap.almoxarifado.pedidos.print-page';

    public Pedidos $record;

    public function mount(Pedidos $record): void
    {
        $this->record = $record->load([
            'solicitante_get',
            'responsavel_atendimento',
            'setor_get',
            'complementoSetor',
            'itens.materialRel',
            'itens.descricaoDetalhadaRel',
        ]);
    }

    protected function getViewData(): array
    {
        return [
            'pedido' => $this->record,
        ];
    }
}
