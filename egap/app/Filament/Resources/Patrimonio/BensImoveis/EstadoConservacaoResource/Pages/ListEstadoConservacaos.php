<?php

namespace App\Filament\Resources\Patrimonio\BensImoveis\EstadoConservacaoResource\Pages;

use App\Filament\Resources\Patrimonio\BensImoveis\EstadoConservacaoResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListEstadoConservacaos extends ListRecords
{
    protected static string $resource = EstadoConservacaoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Novo')
                ->modalHeading('Adicionar Estado de Conservação')
                ->modalWidth('md')
                ->createAnother(false),
        ];
    }
}
