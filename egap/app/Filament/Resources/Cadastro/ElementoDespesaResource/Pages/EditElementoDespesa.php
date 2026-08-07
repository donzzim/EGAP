<?php

namespace App\Filament\Resources\Cadastro\ElementoDespesaResource\Pages;

use App\Filament\Resources\Cadastro\ElementoDespesaResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditElementoDespesa extends EditRecord
{
    protected static string $resource = ElementoDespesaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
