<?php

namespace App\Filament\Resources\Patrimonio\BensImoveis\CedidoResource\Pages;

use Filament\Actions\CreateAction;
use App\Filament\Resources\Patrimonio\BensImoveis\CedidoResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCedidos extends ListRecords
{
    protected static string $resource = CedidoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Novo')
        ];
    }
}
