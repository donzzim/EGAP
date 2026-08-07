<?php

namespace App\Filament\Resources\Patrimonio\BensImoveis\BemImovelResource\Pages;

use App\Filament\Resources\Patrimonio\BensImoveis\BemImovelResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListBemImovels extends ListRecords
{
    protected static string $resource = BemImovelResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Novo'),
        ];
    }
}
