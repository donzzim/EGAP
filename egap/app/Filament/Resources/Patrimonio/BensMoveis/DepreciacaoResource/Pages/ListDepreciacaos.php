<?php

namespace App\Filament\Egap\Resources\Patrimonio\BensMoveis\DepreciacaoResource\Pages;

use App\Filament\Egap\Resources\Patrimonio\BensMoveis\DepreciacaoResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDepreciacaos extends ListRecords
{
    protected static string $resource = DepreciacaoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
