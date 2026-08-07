<?php

namespace App\Filament\Resources\Agendamento\EquipeResource\Pages;

use App\Filament\Resources\Agendamento\EquipeResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditEquipe extends EditRecord
{
    protected static string $resource = EquipeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
