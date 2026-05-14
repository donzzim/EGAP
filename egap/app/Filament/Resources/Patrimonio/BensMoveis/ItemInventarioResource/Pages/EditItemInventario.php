<?php

namespace App\Filament\Egap\Resources\Patrimonio\BensMoveis\ItemInventarioResource\Pages;

use App\Filament\Egap\Resources\Patrimonio\BensMoveis\ItemInventarioResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditItemInventario extends EditRecord
{
    protected static string $resource = ItemInventarioResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}