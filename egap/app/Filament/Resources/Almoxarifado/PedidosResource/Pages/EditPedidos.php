<?php

namespace App\Filament\Egap\Resources\Almoxarifado\PedidosResource\Pages;

use App\Filament\Egap\Resources\Almoxarifado\PedidosResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPedidos extends EditRecord
{
    protected static string $resource = PedidosResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['date_time'] = now();

        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
