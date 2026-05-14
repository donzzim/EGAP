<?php

namespace App\Filament\Resources\Cadastro\ElementoDespesaResource\Pages;

use App\Filament\Resources\Cadastro\ElementoDespesaResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListElementoDespesas extends ListRecords
{
    protected static string $resource = ElementoDespesaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
