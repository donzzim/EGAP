<?php

namespace App\Filament\Resources\Almoxarifado\PedidosResource\Pages;

use App\Filament\Resources\Almoxarifado\PedidosResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePedidos extends CreateRecord
{
    protected static string $resource = PedidosResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['date_time'] = now();

        return $data;
    }
}
