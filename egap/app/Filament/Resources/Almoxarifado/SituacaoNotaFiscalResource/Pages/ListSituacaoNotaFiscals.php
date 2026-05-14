<?php

namespace App\Filament\Resources\Almoxarifado\SituacaoNotaFiscalResource\Pages;

use App\Filament\Resources\Almoxarifado\SituacaoNotaFiscalResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSituacaoNotaFiscals extends ListRecords
{
    protected static string $resource = SituacaoNotaFiscalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
