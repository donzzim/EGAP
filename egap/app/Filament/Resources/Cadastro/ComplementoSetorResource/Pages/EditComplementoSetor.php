<?php

namespace App\Filament\Egap\Resources\Cadastro\ComplementoSetorResource\Pages;

use App\Filament\Egap\Resources\Cadastro\ComplementoSetorResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditComplementoSetor extends EditRecord
{
    protected static string $resource = ComplementoSetorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
