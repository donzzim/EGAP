<?php

namespace App\Filament\Resources\Cadastro\SetoresResource\Pages;

use App\Filament\Resources\Cadastro\SetoresResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListSetores extends ListRecords
{
    protected static string $resource = SetoresResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Novo'),
        ];
    }
}
