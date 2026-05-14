<?php

namespace App\Filament\Resources\Patrimonio\BensImoveis\EstadoConservacaoResource\Pages;

use App\Filament\Resources\Patrimonio\BensImoveis\EstadoConservacaoResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListEstadoConservacaos extends ListRecords
{
    protected static string $resource = EstadoConservacaoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Adicionar')
                ->icon('heroicon-o-plus-circle')
                ->modalHeading('Adicionar Estado de Conservação')
                ->modalWidth('md')
                ->createAnother(false),
        ];
    }
}
