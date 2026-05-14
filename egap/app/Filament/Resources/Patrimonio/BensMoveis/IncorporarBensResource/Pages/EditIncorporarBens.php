<?php

namespace App\Filament\Resources\Patrimonio\BensMoveis\IncorporarBensResource\Pages;

use App\Filament\Resources\Patrimonio\BensMoveis\IncorporarBensResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditIncorporarBens extends EditRecord
{
    protected static string $resource = IncorporarBensResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
