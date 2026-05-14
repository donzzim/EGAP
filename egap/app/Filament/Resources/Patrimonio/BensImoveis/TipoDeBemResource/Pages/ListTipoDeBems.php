<?php

namespace App\Filament\Resources\Patrimonio\BensImoveis\TipoDeBemResource\Pages;

use App\Filament\Resources\Patrimonio\BensImoveis\TipoDeBemResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTipoDeBems extends ListRecords
{
    protected static string $resource = TipoDeBemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Adicionar')
                ->icon('heroicon-o-plus-circle')
                ->modalHeading('Adicionar Tipo de bem')
                ->modalWidth('md')
                ->createAnother(false),
        ];
    }
}
