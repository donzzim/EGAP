<?php

namespace App\Filament\Resources\Patrimonio\BensImoveis\ReavaliacaoResource\Pages;

use App\Filament\Resources\Patrimonio\BensImoveis\ReavaliacaoResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListReavaliacoes extends ListRecords
{
    protected static string $resource = ReavaliacaoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Adicionar')
                ->icon('heroicon-o-plus-circle')
                ->modalHeading('Adicionar Reavaliação')
                ->modalWidth('7xl')
                ->createAnother(false),
        ];
    }
}
