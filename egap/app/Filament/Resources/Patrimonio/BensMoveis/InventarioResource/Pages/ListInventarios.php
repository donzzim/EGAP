<?php

namespace App\Filament\Resources\Patrimonio\BensMoveis\InventarioResource\Pages;

use App\Filament\Resources\Patrimonio\BensMoveis\InventarioResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListInventarios extends ListRecords
{
    protected static string $resource = InventarioResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Novo'),
        ];
    }
}
