<?php

namespace App\Filament\Resources\Patrimonio\BensMoveis\TermoResource\Pages;

use App\Filament\Resources\Patrimonio\BensMoveis\TermoResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTermo extends EditRecord
{
    protected static string $resource = TermoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
