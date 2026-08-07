<?php

namespace App\Filament\Resources\Almoxarifado\NotaFiscalResource\Pages;

use App\Filament\Resources\Almoxarifado\NotaFiscalResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListNotaFiscals extends ListRecords
{
    protected static string $resource = NotaFiscalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Novo'),
        ];
    }
}
