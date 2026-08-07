<?php

namespace App\Filament\Resources\Patrimonio\BensImoveis\ResponsavelResource\Pages;

use Filament\Actions\CreateAction;
use App\Filament\Resources\Patrimonio\BensImoveis\ResponsavelResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListResponsavels extends ListRecords
{
    protected static string $resource = ResponsavelResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Novo')
                ->modalHeading('Adicionar Responsável')
                ->modalWidth('md')
                ->createAnother(false),
        ];
    }
}
