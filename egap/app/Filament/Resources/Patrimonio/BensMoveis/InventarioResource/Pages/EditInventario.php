<?php

namespace App\Filament\Egap\Resources\Patrimonio\BensMoveis\InventarioResource\Pages;

use App\Filament\Egap\Resources\Patrimonio\BensMoveis\InventarioResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditInventario extends EditRecord
{
    protected static string $resource = InventarioResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}