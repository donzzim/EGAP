<?php

namespace App\Filament\Egap\Resources\Patrimonio\BensImoveis\ResponsavelResource\Pages;

use App\Filament\Egap\Resources\Patrimonio\BensImoveis\ResponsavelResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListResponsavels extends ListRecords
{
    protected static string $resource = ResponsavelResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Adicionar')
                ->icon('heroicon-o-plus-circle')
                ->modalHeading('Adicionar Responsável')
                ->modalWidth('md')
                ->createAnother(false),
        ];
    }
}
