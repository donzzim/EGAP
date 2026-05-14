<?php

namespace App\Filament\Resources\Patrimonio\BensImoveis\DenominacaoResource\Pages;

use App\Filament\Resources\Patrimonio\BensImoveis\DenominacaoResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDenominacaos extends ListRecords
{
    protected static string $resource = DenominacaoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Adicionar')
                ->icon('heroicon-o-plus-circle')
                ->modalHeading('Adicionar Denominação')
                ->modalWidth('md')
                ->createAnother(false),
        ];
    }
}
