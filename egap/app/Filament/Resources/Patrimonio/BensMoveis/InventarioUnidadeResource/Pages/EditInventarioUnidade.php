<?php

namespace App\Filament\Resources\Patrimonio\BensMoveis\InventarioUnidadeResource\Pages;

use App\Filament\Resources\Patrimonio\BensMoveis\InventarioUnidadeResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditInventarioUnidade extends EditRecord
{
    protected static string $resource = InventarioUnidadeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
