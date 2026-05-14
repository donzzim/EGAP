<?php

namespace App\Filament\Egap\Resources\Patrimonio\BensMoveis\ValidarTermoResource\Pages;

use App\Filament\Egap\Resources\Patrimonio\BensMoveis\ValidarTermoResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditValidarTermo extends EditRecord
{
    protected static string $resource = ValidarTermoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
