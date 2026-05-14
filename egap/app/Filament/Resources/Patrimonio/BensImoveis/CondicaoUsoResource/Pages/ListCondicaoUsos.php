<?php

namespace App\Filament\Egap\Resources\Patrimonio\BensImoveis\CondicaoUsoResource\Pages;

use App\Filament\Egap\Resources\Patrimonio\BensImoveis\CondicaoUsoResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCondicaoUsos extends ListRecords
{
    protected static string $resource = CondicaoUsoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Adicionar')
                ->icon('heroicon-o-plus-circle')
                ->modalHeading('Adicionar')
                ->modalWidth('md')
                ->createAnother(false),
        ];
    }
}
