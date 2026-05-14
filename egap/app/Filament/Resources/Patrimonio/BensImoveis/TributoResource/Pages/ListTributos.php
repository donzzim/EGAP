<?php

namespace App\Filament\Egap\Resources\Patrimonio\BensImoveis\TributoResource\Pages;

use App\Filament\Egap\Resources\Patrimonio\BensImoveis\TributoResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTributos extends ListRecords
{
    protected static string $resource = TributoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Adicionar')
                ->icon('heroicon-o-plus-circle')
                ->modalHeading('Adicionar Tributo')
                ->modalWidth('4xl')
                ->createAnother(false),
        ];
    }
}
