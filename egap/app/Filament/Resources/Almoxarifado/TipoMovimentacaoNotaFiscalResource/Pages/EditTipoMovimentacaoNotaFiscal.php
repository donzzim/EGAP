<?php

namespace App\Filament\Egap\Resources\Almoxarifado\TipoMovimentacaoNotaFiscalResource\Pages;

use App\Filament\Egap\Resources\Almoxarifado\TipoMovimentacaoNotaFiscalResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTipoMovimentacaoNotaFiscal extends EditRecord
{
    protected static string $resource = TipoMovimentacaoNotaFiscalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
