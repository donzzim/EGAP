<?php

namespace App\Filament\Resources\Patrimonio\BensImoveis\DenominacaoResource\Pages;

use App\Filament\Resources\Patrimonio\BensImoveis\DenominacaoResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListDenominacaos extends ListRecords
{
    protected static string $resource = DenominacaoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Novo')
                ->modalHeading('Adicionar Denominação')
                ->modalWidth('md')
                ->createAnother(false),
        ];
    }
}
