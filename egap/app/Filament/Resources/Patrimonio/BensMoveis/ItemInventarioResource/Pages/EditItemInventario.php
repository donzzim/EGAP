<?php

namespace App\Filament\Resources\Patrimonio\BensMoveis\ItemInventarioResource\Pages;

use App\Filament\Resources\Patrimonio\BensMoveis\ItemInventarioResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditItemInventario extends EditRecord
{
    protected static string $resource = ItemInventarioResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
