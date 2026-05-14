<?php

namespace App\Filament\Egap\Resources\Processo\TipoProcessoResource\Pages;

use App\Filament\Egap\Resources\Processo\TipoProcessoResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTipoProcessos extends ListRecords
{
    protected static string $resource = TipoProcessoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            //
        ];
    }
}