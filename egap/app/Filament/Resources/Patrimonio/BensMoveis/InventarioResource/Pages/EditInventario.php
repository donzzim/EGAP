<?php

namespace App\Filament\Resources\Patrimonio\BensMoveis\InventarioResource\Pages;

use Filament\Actions\DeleteAction;
use App\Filament\Resources\Patrimonio\BensMoveis\InventarioResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditInventario extends EditRecord
{
    protected static string $resource = InventarioResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
