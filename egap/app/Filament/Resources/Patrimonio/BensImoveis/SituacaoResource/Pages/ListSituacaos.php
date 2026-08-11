<?php

namespace App\Filament\Resources\Patrimonio\BensImoveis\SituacaoResource\Pages;

use Filament\Actions\CreateAction;
use App\Filament\Resources\Patrimonio\BensImoveis\SituacaoResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSituacaos extends ListRecords
{
    protected static string $resource = SituacaoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Novo')
                ->modalHeading('Adicionar Situação')
                ->modalWidth('md')
                ->createAnother(false),
        ];
    }
}
