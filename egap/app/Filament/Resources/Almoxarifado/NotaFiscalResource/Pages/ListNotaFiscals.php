<?php

namespace App\Filament\Egap\Resources\Almoxarifado\NotaFiscalResource\Pages;

use App\Filament\Egap\Resources\Almoxarifado\NotaFiscalResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListNotaFiscals extends ListRecords
{
    protected static string $resource = NotaFiscalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
