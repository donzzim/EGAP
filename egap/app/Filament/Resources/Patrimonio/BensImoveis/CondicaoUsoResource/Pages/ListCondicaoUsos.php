<?php

namespace App\Filament\Resources\Patrimonio\BensImoveis\CondicaoUsoResource\Pages;

use App\Filament\Resources\Patrimonio\BensImoveis\CondicaoUsoResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCondicaoUsos extends ListRecords
{
    protected static string $resource = CondicaoUsoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Novo')
                ->modalHeading('Adicionar')
                ->modalWidth('md')
                ->createAnother(false),
        ];
    }
}
