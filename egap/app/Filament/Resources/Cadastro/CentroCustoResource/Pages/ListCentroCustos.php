<?php

namespace App\Filament\Resources\Cadastro\CentroCustoResource\Pages;

use App\Filament\Resources\Cadastro\CentroCustoResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCentroCustos extends ListRecords
{
    protected static string $resource = CentroCustoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Novo'),
        ];
    }
}
