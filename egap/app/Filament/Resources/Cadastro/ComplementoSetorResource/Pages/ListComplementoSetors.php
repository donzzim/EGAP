<?php

namespace App\Filament\Resources\Cadastro\ComplementoSetorResource\Pages;

use App\Filament\Resources\Cadastro\ComplementoSetorResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListComplementoSetors extends ListRecords
{
    protected static string $resource = ComplementoSetorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
