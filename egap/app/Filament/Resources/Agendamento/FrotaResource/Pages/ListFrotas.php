<?php

namespace App\Filament\Resources\Agendamento\FrotaResource\Pages;

use App\Filament\Resources\Agendamento\FrotaResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListFrotas extends ListRecords
{
    protected static string $resource = FrotaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
