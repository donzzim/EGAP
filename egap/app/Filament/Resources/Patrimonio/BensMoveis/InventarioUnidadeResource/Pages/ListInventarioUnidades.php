<?php

namespace App\Filament\Resources\Patrimonio\BensMoveis\InventarioUnidadeResource\Pages;

use App\Filament\Resources\Patrimonio\BensMoveis\InventarioUnidadeResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListInventarioUnidades extends ListRecords
{
    protected static string $resource = InventarioUnidadeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Novo'),
        ];
    }
}
