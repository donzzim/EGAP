<?php

namespace App\Filament\Resources\Agendamento\RegiaoResource\Pages;

use App\Filament\Resources\Agendamento\RegiaoResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListRegiaos extends ListRecords
{
    protected static string $resource = RegiaoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Novo'),
        ];
    }
}
