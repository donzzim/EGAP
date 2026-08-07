<?php

namespace App\Filament\Resources\Almoxarifado\TipoMovimentacaoNotaFiscalResource\Pages;

use App\Filament\Resources\Almoxarifado\TipoMovimentacaoNotaFiscalResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListTipoMovimentacaoNotaFiscals extends ListRecords
{
    protected static string $resource = TipoMovimentacaoNotaFiscalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Novo'),
        ];
    }
}
