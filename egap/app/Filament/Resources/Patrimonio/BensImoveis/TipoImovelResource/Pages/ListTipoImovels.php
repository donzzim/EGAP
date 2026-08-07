<?php

namespace App\Filament\Resources\Patrimonio\BensImoveis\TipoImovelResource\Pages;

use App\Filament\Resources\Patrimonio\BensImoveis\TipoImovelResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListTipoImovels extends ListRecords
{
    protected static string $resource = TipoImovelResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Novo')
                ->modalHeading('Adicionar Tipo de Imóvel')
                ->modalWidth('md')
                ->createAnother(false),
        ];
    }
}
