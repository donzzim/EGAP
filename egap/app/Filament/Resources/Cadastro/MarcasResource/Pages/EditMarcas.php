<?php

namespace App\Filament\Egap\Resources\Cadastro\MarcasResource\Pages;

use App\Filament\Egap\Resources\Cadastro\MarcasResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMarcas extends EditRecord
{
    protected static string $resource = MarcasResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
