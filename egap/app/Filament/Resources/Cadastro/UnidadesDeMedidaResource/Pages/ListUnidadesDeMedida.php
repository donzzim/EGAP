<?php

namespace App\Filament\Egap\Resources\Cadastro\UnidadesDeMedidaResource\Pages;

use App\Filament\Egap\Resources\Cadastro\UnidadesDeMedidaResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListUnidadesDeMedida extends ListRecords
{
    protected static string $resource = UnidadesDeMedidaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    protected function getCreateButtonLabel(): string
    {
        return 'Cadastrar';
    }
}
