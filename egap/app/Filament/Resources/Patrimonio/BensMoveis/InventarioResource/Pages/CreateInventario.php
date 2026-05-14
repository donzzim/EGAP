<?php

namespace App\Filament\Resources\Patrimonio\BensMoveis\InventarioResource\Pages;

use App\Filament\Resources\Patrimonio\BensMoveis\InventarioResource;
use Filament\Resources\Pages\CreateRecord;

class CreateInventario extends CreateRecord
{
    protected static string $resource = InventarioResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
