<?php

namespace App\Filament\Resources\Agendamento\TransporteResource\Pages;

use App\Filament\Resources\Agendamento\TransporteResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTransportes extends ListRecords
{
    protected static string $resource = TransporteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
