<?php

namespace App\Filament\Egap\Resources\Agendamento\EquipeResource\Pages;

use App\Filament\Egap\Resources\Agendamento\EquipeResource;
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
