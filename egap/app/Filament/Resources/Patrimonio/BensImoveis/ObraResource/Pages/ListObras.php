<?php

namespace App\Filament\Resources\Patrimonio\BensImoveis\ObraResource\Pages;

use App\Filament\Resources\Patrimonio\BensImoveis\ObraResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListObras extends ListRecords
{
    protected static string $resource = ObraResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Novo')
                ->modalHeading('Adicionar Obras e Ampliações')
                ->modalWidth('md')
                ->createAnother(false),
        ];
    }
}
