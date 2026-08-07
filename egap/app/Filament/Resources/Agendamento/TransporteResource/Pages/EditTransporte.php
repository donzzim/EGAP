<?php

namespace App\Filament\Resources\Agendamento\TransporteResource\Pages;

use App\Filament\Resources\Agendamento\TransporteResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditTransporte extends EditRecord
{
    protected static string $resource = TransporteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
