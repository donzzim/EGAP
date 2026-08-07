<?php

namespace App\Filament\Resources\Almoxarifado\SituacaoNotaFiscalResource\Pages;

use Filament\Actions\CreateAction;
use App\Filament\Resources\Almoxarifado\SituacaoNotaFiscalResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSituacaoNotaFiscals extends ListRecords
{
    protected static string $resource = SituacaoNotaFiscalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Novo')
        ];
    }
}
