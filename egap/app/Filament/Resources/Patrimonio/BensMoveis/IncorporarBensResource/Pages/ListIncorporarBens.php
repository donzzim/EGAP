<?php

namespace App\Filament\Resources\Patrimonio\BensMoveis\IncorporarBensResource\Pages;

use App\Filament\Resources\Patrimonio\BensMoveis\IncorporarBensResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListIncorporarBens extends ListRecords
{
    protected static string $resource = IncorporarBensResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Novo')
        ];
    }
}
