<?php

namespace App\Filament\Resources\Patrimonio\BensImoveis\TributoResource\Pages;

use App\Filament\Resources\Patrimonio\BensImoveis\TributoResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTributos extends ListRecords
{
    protected static string $resource = TributoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Novo')
                ->modalHeading('Adicionar Tributo')
                ->modalWidth('4xl')
                ->createAnother(false),
        ];
    }
}
