<?php

namespace App\Filament\Egap\Resources\Agendamento\RegiaoResource\Pages;

use App\Filament\Egap\Resources\Agendamento\RegiaoResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditRegiao extends EditRecord
{
    protected static string $resource = RegiaoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
