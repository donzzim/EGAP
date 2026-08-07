<?php

namespace App\Filament\Resources\Patrimonio\BensMoveis\TermoResource\Pages;

use Filament\Actions\CreateAction;
use App\Filament\Resources\Patrimonio\BensMoveis\TermoResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTermos extends ListRecords
{
    protected static string $resource = TermoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Novo')
        ];
    }
}
