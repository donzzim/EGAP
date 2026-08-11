<?php

namespace App\Filament\Resources\Almoxarifado\SituacaoNotaFiscalResource\Pages;

use Filament\Actions\DeleteAction;
use App\Filament\Resources\Almoxarifado\SituacaoNotaFiscalResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSituacaoNotaFiscal extends EditRecord
{
    protected static string $resource = SituacaoNotaFiscalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
