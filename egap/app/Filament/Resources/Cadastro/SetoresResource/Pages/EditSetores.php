<?php

namespace App\Filament\Egap\Resources\Cadastro\SetoresResource\Pages;

use App\Filament\Egap\Resources\Cadastro\SetoresResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSetores extends EditRecord
{
    protected static string $resource = SetoresResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
