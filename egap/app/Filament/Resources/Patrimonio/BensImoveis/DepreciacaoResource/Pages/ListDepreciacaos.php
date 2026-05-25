<?php

namespace App\Filament\Resources\Patrimonio\BensImoveis\DepreciacaoResource\Pages;

use App\Filament\Resources\Patrimonio\BensImoveis\DepreciacaoResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDepreciacaos extends ListRecords
{
    protected static string $resource = DepreciacaoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Novo')
                ->modalHeading('Adicionar Depreciação Imóveis')
                ->modalWidth('lg')
                ->createAnother(false),
        ];
    }
}
