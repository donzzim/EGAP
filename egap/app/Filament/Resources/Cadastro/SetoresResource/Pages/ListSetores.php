<?php

namespace App\Filament\Egap\Resources\Cadastro\SetoresResource\Pages;

use App\Filament\Egap\Resources\Cadastro\SetoresResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSetores extends ListRecords
{
    protected static string $resource = SetoresResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
