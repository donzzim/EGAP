<?php

namespace App\Filament\Resources\Cadastro\SetoresResource\Pages;

use Filament\Actions\CreateAction;
use App\Filament\Resources\Cadastro\SetoresResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSetores extends ListRecords
{
    protected static string $resource = SetoresResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Novo')
        ];
    }
}
