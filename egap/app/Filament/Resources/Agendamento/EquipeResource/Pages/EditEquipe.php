<?php

namespace App\Filament\Egap\Resources\Agendamento\EquipeResource\Pages;

use App\Filament\Egap\Resources\Agendamento\EquipeResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditEquipe extends EditRecord
{
    protected static string $resource = EquipeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
