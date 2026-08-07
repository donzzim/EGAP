<?php

namespace App\Filament\Resources\Patrimonio\BensMoveis\TermoResource\Pages;

use App\Filament\Resources\Patrimonio\BensMoveis\TermoResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditTermo extends EditRecord
{
    protected static string $resource = TermoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
