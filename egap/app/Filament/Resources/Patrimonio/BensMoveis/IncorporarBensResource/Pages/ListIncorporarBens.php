<?php

namespace App\Filament\Egap\Resources\Patrimonio\BensMoveis\IncorporarBensResource\Pages;

use App\Filament\Egap\Resources\Patrimonio\BensMoveis\IncorporarBensResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListIncorporarBens extends ListRecords
{
    protected static string $resource = IncorporarBensResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
