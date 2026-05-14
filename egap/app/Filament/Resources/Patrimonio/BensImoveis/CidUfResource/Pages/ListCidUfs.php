<?php

namespace App\Filament\Resources\Patrimonio\BensImoveis\CidUfResource\Pages;

use App\Filament\Resources\Patrimonio\BensImoveis\CidUfResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCidUfs extends ListRecords
{
    protected static string $resource = CidUfResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Adicionar')
                ->icon('heroicon-o-plus-circle')
                ->modalHeading('Adicionar Cidade/UF')
                ->modalWidth('md')
                ->createAnother(false),
        ];
    }
}
