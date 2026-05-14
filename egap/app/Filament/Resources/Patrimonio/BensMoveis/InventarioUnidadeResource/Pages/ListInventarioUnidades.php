<?php

namespace App\Filament\Egap\Resources\Patrimonio\BensMoveis\InventarioUnidadeResource\Pages;

use App\Filament\Egap\Resources\Patrimonio\BensMoveis\InventarioUnidadeResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListInventarioUnidades extends ListRecords
{
    protected static string $resource = InventarioUnidadeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}