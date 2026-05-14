<?php

namespace App\Filament\Resources\Cadastro\MarcasResource\Pages;

use App\Filament\Resources\Cadastro\MarcasResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMarcas extends ListRecords
{
    protected static string $resource = MarcasResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
