<?php

namespace App\Filament\Resources\Almoxarifado\PedidosResource\Pages;

use App\Filament\Resources\Almoxarifado\PedidosResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPedidos extends ListRecords
{
    protected static string $resource = PedidosResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Novo'),
        ];
    }
}
