<?php

namespace App\Filament\Resources\Patrimonio\BensMoveis\AtividadeInventarioResource\Pages;

use Filament\Actions\DeleteAction;
use App\Filament\Resources\Patrimonio\BensMoveis\AtividadeInventarioResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAtividadeInventario extends EditRecord
{
    protected static string $resource = AtividadeInventarioResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
