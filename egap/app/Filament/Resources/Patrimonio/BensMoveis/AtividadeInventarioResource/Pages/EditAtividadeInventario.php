<?php

namespace App\Filament\Egap\Resources\Patrimonio\BensMoveis\AtividadeInventarioResource\Pages;

use App\Filament\Egap\Resources\Patrimonio\BensMoveis\AtividadeInventarioResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAtividadeInventario extends EditRecord
{
    protected static string $resource = AtividadeInventarioResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
