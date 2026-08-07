<?php

namespace App\Filament\Resources\Cadastro\UnidadesDeMedidaResource\Pages;

use Filament\Actions\CreateAction;
use App\Filament\Resources\Cadastro\UnidadesDeMedidaResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListUnidadesDeMedida extends ListRecords
{
    protected static string $resource = UnidadesDeMedidaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Novo')
        ];
    }
}
