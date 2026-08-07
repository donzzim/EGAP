<?php

namespace App\Filament\Resources\Patrimonio\BensMoveis\BaixaResource\Pages;

use App\Filament\Resources\Patrimonio\BensMoveis\BaixaResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListBaixas extends ListRecords
{
    protected static string $resource = BaixaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Novo'),
        ];
    }
}
