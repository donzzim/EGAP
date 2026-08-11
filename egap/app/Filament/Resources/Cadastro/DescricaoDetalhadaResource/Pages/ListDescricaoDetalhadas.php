<?php

namespace App\Filament\Resources\Cadastro\DescricaoDetalhadaResource\Pages;

use Filament\Actions\CreateAction;
use App\Filament\Resources\Cadastro\DescricaoDetalhadaResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDescricaoDetalhadas extends ListRecords
{
    protected static string $resource = DescricaoDetalhadaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Novo')
        ];
    }
}
