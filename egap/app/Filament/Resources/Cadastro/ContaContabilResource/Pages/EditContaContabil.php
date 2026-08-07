<?php

namespace App\Filament\Resources\Cadastro\ContaContabilResource\Pages;

use App\Filament\Resources\Cadastro\ContaContabilResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditContaContabil extends EditRecord
{
    protected static string $resource = ContaContabilResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
