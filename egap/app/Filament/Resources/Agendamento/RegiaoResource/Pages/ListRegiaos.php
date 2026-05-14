<?php

namespace App\Filament\Egap\Resources\Agendamento\RegiaoResource\Pages;

use App\Filament\Egap\Resources\Agendamento\RegiaoResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListRegiaos extends ListRecords
{
    protected static string $resource = RegiaoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
