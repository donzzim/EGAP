<?php

namespace App\Filament\Resources\Patrimonio\BensImoveis\TipoTributoResource\Pages;

use App\Filament\Resources\Patrimonio\BensImoveis\TipoTributoResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListTipoTributos extends ListRecords
{
    protected static string $resource = TipoTributoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Novo'),
        ];
    }
}
