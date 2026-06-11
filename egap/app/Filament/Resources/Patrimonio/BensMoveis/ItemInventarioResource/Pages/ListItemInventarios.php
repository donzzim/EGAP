<?php

namespace App\Filament\Resources\Patrimonio\BensMoveis\ItemInventarioResource\Pages;

use App\Filament\Resources\Patrimonio\BensMoveis\ItemInventarioResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListItemInventarios extends ListRecords
{
    protected static string $resource = ItemInventarioResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Novo'),
        ];
    }
}
