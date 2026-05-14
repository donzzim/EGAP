<?php

namespace App\Filament\Egap\Resources\Almoxarifado\PedidosResource\Pages;

use App\Filament\Egap\Resources\Almoxarifado\PedidosResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPedidos extends ListRecords
{
    protected static string $resource = PedidosResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
