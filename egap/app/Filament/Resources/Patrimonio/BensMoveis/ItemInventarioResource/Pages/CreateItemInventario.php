<?php

namespace App\Filament\Resources\Patrimonio\BensMoveis\ItemInventarioResource\Pages;

use App\Filament\Resources\Patrimonio\BensMoveis\ItemInventarioResource;
use Filament\Resources\Pages\CreateRecord;

class CreateItemInventario extends CreateRecord
{
    protected static string $resource = ItemInventarioResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
