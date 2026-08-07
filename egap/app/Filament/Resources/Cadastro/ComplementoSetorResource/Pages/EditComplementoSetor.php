<?php

namespace App\Filament\Resources\Cadastro\ComplementoSetorResource\Pages;

use App\Filament\Resources\Cadastro\ComplementoSetorResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditComplementoSetor extends EditRecord
{
    protected static string $resource = ComplementoSetorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
