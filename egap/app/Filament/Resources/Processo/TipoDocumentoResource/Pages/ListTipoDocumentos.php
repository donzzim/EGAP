<?php

namespace App\Filament\Resources\Processo\TipoDocumentoResource\Pages;

use Filament\Actions\CreateAction;
use App\Filament\Resources\Processo\TipoDocumentoResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTipoDocumentos extends ListRecords
{
    protected static string $resource = TipoDocumentoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Novo')
        ];
    }
}
