<?php

namespace App\Filament\Egap\Resources\Patrimonio\BensMoveis\InventarioResource\Pages;

use App\Filament\Egap\Resources\Patrimonio\BensMoveis\InventarioResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListInventarios extends ListRecords
{
    protected static string $resource = InventarioResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}