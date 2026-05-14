<?php

namespace App\Filament\Resources\Cadastro\DescricaoResumidaResource\Pages;

use App\Filament\Resources\Cadastro\DescricaoResumidaResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDescricaoResumidas extends ListRecords
{
    protected static string $resource = DescricaoResumidaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
