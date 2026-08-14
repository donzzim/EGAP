<?php

namespace App\Filament\Resources\Patrimonio\BensImoveis\BemImovelResource\Pages;

use Filament\Actions\CreateAction;
use App\Filament\Resources\Patrimonio\BensImoveis\BemImovelResource;
use App\Filament\Resources\Patrimonio\BensImoveis\BemImovelResource\Widgets\BensImoveisCountStats;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListBemImovels extends ListRecords
{
    protected static string $resource = BemImovelResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Novo')
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            BensImoveisCountStats::class,
        ];
    }
}
