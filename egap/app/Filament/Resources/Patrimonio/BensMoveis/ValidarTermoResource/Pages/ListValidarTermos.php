<?php

namespace App\Filament\Resources\Patrimonio\BensMoveis\ValidarTermoResource\Pages;

use App\Filament\Resources\Patrimonio\BensMoveis\ValidarTermoResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListValidarTermos extends ListRecords
{
    protected static string $resource = ValidarTermoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Novo'),
        ];
    }
}
