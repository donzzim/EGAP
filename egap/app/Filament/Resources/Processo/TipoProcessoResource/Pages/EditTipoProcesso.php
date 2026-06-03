<?php

namespace App\Filament\Resources\Processo\TipoProcessoResource\Pages;

use App\Filament\Resources\Processo\TipoProcessoResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTipoProcesso extends EditRecord
{
    protected static string $resource = TipoProcessoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            //
        ];
    }
}
