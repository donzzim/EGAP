<?php

namespace App\Filament\Resources\Cadastro\MarcasResource\Pages;

use App\Filament\Resources\Cadastro\MarcasResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditMarcas extends EditRecord
{
    protected static string $resource = MarcasResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
