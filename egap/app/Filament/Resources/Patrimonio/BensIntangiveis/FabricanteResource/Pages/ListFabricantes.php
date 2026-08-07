<?php

namespace App\Filament\Resources\Patrimonio\BensIntangiveis\FabricanteResource\Pages;

use App\Filament\Resources\Patrimonio\BensIntangiveis\FabricanteResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListFabricantes extends ListRecords
{
    protected static string $resource = FabricanteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Novo'),
        ];
    }
}
