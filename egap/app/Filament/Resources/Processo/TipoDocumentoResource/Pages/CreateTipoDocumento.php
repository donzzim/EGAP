<?php

namespace App\Filament\Egap\Resources\Processo\TipoDocumentoResource\Pages;

use App\Filament\Egap\Resources\Processo\TipoDocumentoResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateTipoDocumento extends CreateRecord
{
    protected static string $resource = TipoDocumentoResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}