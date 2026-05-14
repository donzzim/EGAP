<?php

namespace App\Filament\Egap\Resources\Patrimonio\BensMoveis\ItemInventarioResource\Pages;

use App\Filament\Egap\Resources\Patrimonio\BensMoveis\ItemInventarioResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListItemInventarios extends ListRecords
{
    protected static string $resource = ItemInventarioResource::class;

    protected function getHeaderActions(): array
    {
        return [
            /** ✅ BOTÃO "NOVO" ADICIONADO */
            Actions\CreateAction::make()
                ->label('New Material'),
        ];
    }
}