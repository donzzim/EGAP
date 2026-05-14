<?php

namespace App\Filament\Resources\Patrimonio\BensIntangiveis\FabricanteResource\Pages;

use App\Filament\Resources\Patrimonio\BensIntangiveis\FabricanteResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditFabricante extends EditRecord
{
    protected static string $resource = FabricanteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
