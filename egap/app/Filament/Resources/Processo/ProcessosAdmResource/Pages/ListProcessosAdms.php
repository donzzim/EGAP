<?php

namespace App\Filament\Egap\Resources\Processo\ProcessosAdmResource\Pages;

use App\Filament\Egap\Resources\Processo\ProcessosAdmResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListProcessosAdms extends ListRecords
{
    protected static string $resource = ProcessosAdmResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Adicionar')
                ->icon('heroicon-o-plus-circle')
                ->modalHeading('Adicionar Processo')
                ->modalWidth('4xl')
                ->createAnother(false),
        ];
    }
}
