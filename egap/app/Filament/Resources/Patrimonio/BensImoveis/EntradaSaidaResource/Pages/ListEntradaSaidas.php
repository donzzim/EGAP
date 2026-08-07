<?php

namespace App\Filament\Resources\Patrimonio\BensImoveis\EntradaSaidaResource\Pages;

use App\Filament\Resources\Patrimonio\BensImoveis\EntradaSaidaResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListEntradaSaidas extends ListRecords
{
    protected static string $resource = EntradaSaidaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Novo')
                ->modalHeading('Adicionar Entrada/Saída')
                ->modalWidth('md')
                ->createAnother(false),
        ];
    }
}
