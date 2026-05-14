<?php

namespace App\Filament\Egap\Resources\Patrimonio\BensImoveis\TipoImovelResource\Pages;

use App\Filament\Egap\Resources\Patrimonio\BensImoveis\TipoImovelResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTipoImovels extends ListRecords
{
    protected static string $resource = TipoImovelResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Adicionar')
                ->icon('heroicon-o-plus-circle')
                ->modalHeading('Adicionar Tipo de Imóvel')
                ->modalWidth('md')
                ->createAnother(false),
        ];
    }
}
