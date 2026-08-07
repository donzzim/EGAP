<?php

namespace App\Filament\Resources\Patrimonio\BensMoveis\DepreciacaoResource\Pages;

use Filament\Actions\CreateAction;
use App\Filament\Resources\Patrimonio\BensMoveis\DepreciacaoResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDepreciacaos extends ListRecords
{
    protected static string $resource = DepreciacaoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Novo'),
        ];
    }
}
