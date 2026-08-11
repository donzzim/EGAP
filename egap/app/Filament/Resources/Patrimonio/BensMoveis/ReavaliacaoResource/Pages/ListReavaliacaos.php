<?php

namespace App\Filament\Resources\Patrimonio\BensMoveis\ReavaliacaoResource\Pages;

use Filament\Actions\CreateAction;
use App\Filament\Resources\Patrimonio\BensMoveis\ReavaliacaoResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListReavaliacaos extends ListRecords
{
    protected static string $resource = ReavaliacaoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Novo'),
        ];
    }
}
