<?php

namespace App\Filament\Egap\Resources\Agendamento\FrotaResource\Pages;

use App\Filament\Egap\Resources\Agendamento\FrotaResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditFrota extends EditRecord
{
    protected static string $resource = FrotaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
