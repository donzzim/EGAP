<?php

namespace App\Filament\Egap\Resources\Cadastro\DescricaoDetalhadaResource\Pages;

use App\Filament\Egap\Resources\Cadastro\DescricaoDetalhadaResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDescricaoDetalhadas extends ListRecords
{
    protected static string $resource = DescricaoDetalhadaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
