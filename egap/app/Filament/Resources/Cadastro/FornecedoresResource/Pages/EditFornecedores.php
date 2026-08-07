<?php

namespace App\Filament\Resources\Cadastro\FornecedoresResource\Pages;

use App\Filament\Resources\Cadastro\FornecedoresResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditFornecedores extends EditRecord
{
    protected static string $resource = FornecedoresResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
