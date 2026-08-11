<?php

namespace App\Filament\Resources\Agendamento\FrotaResource\Pages;

use Filament\Actions\CreateAction;
use App\Filament\Resources\Agendamento\FrotaResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListFrotas extends ListRecords
{
    protected static string $resource = FrotaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Novo')
        ];
    }
}
