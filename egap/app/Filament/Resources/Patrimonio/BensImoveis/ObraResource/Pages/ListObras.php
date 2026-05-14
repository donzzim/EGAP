<?php

namespace App\Filament\Egap\Resources\Patrimonio\BensImoveis\ObraResource\Pages;

use App\Filament\Egap\Resources\Patrimonio\BensImoveis\ObraResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListObras extends ListRecords
{
    protected static string $resource = ObraResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Adicionar')
                ->icon('heroicon-o-plus-circle')
                ->modalHeading('Adicionar Obras e Ampliações')
                ->modalWidth('md')
                ->createAnother(false),
        ];
    }
}
