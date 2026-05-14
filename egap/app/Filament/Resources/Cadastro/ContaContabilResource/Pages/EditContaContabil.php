<?php

namespace App\Filament\Egap\Resources\Cadastro\ContaContabilResource\Pages;

use App\Filament\Egap\Resources\Cadastro\ContaContabilResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditContaContabil extends EditRecord
{
    protected static string $resource = ContaContabilResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
