<?php

namespace App\Filament\Egap\Resources\Almoxarifado\PedidosResource\Pages;

use App\Filament\Egap\Resources\Almoxarifado\PedidosResource;
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
