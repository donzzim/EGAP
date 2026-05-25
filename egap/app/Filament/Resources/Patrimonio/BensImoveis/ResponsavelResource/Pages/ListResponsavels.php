<?php

namespace App\Filament\Resources\Patrimonio\BensImoveis\ResponsavelResource\Pages;

use App\Filament\Resources\Patrimonio\BensImoveis\ResponsavelResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListResponsavels extends ListRecords
{
    protected static string $resource = ResponsavelResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Novo')
                ->modalHeading('Adicionar Responsável')
                ->modalWidth('md')
                ->createAnother(false),
        ];
    }
}
