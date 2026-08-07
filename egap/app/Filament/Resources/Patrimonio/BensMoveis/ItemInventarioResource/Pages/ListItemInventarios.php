<?php

namespace App\Filament\Resources\Patrimonio\BensMoveis\ItemInventarioResource\Pages;

use Filament\Actions\CreateAction;
use App\Filament\Resources\Patrimonio\BensMoveis\ItemInventarioResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListItemInventarios extends ListRecords
{
    protected static string $resource = ItemInventarioResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Novo'),
        ];
    }
}
