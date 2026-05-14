<?php

namespace App\Filament\Egap\Resources\Patrimonio\BensImoveis\CedidoResource\Pages;

use App\Filament\Egap\Resources\Patrimonio\BensImoveis\CedidoResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCedidos extends ListRecords
{
    protected static string $resource = CedidoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Adicionar')
                ->icon('heroicon-o-plus-circle'),
        ];
    }
}
