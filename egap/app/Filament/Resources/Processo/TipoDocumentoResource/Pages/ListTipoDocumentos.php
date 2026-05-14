<?php

namespace App\Filament\Egap\Resources\Processo\TipoDocumentoResource\Pages;

use App\Filament\Egap\Resources\Processo\TipoDocumentoResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTipoDocumentos extends ListRecords
{
    protected static string $resource = TipoDocumentoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Adicionar')
                ->icon('heroicon-o-plus'),
        ];
    }
}