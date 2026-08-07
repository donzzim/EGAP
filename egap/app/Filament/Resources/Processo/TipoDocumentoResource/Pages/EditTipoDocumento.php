<?php

namespace App\Filament\Resources\Processo\TipoDocumentoResource\Pages;

use Filament\Actions\DeleteAction;
use App\Filament\Resources\Processo\TipoDocumentoResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTipoDocumento extends EditRecord
{
    protected static string $resource = TipoDocumentoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
