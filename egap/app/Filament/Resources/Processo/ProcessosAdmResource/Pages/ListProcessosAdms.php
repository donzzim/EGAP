<?php

namespace App\Filament\Resources\Processo\ProcessosAdmResource\Pages;

use App\Filament\Resources\Processo\ProcessosAdmResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListProcessosAdms extends ListRecords
{
    protected static string $resource = ProcessosAdmResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Novo')
                ->modalHeading('Adicionar Processo')
                ->modalWidth('4xl')
                ->createAnother(false),
        ];
    }
}
