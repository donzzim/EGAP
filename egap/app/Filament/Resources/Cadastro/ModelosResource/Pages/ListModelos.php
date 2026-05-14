<?php

namespace App\Filament\Egap\Resources\Cadastro\ModelosResource\Pages;

use App\Filament\Egap\Resources\Cadastro\ModelosResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListModelos extends ListRecords
{
    protected static string $resource = ModelosResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
