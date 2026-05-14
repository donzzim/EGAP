<?php

namespace App\Filament\Resources\Agendamento\EquipeResource\Pages;

use App\Filament\Resources\Agendamento\EquipeResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListEquipes extends ListRecords
{
    protected static string $resource = EquipeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
