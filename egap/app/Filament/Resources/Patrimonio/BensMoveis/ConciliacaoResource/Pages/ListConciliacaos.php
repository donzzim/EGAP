<?php

namespace App\Filament\Resources\Patrimonio\BensMoveis\ConciliacaoResource\Pages;

use App\Filament\Resources\Patrimonio\BensMoveis\ConciliacaoResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListConciliacaos extends ListRecords
{
    protected static string $resource = ConciliacaoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Novo'),
        ];
    }
}
