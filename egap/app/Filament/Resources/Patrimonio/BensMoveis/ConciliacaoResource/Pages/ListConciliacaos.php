<?php

namespace App\Filament\Egap\Resources\Patrimonio\BensMoveis\ConciliacaoResource\Pages;

use App\Filament\Egap\Resources\Patrimonio\BensMoveis\ConciliacaoResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListConciliacaos extends ListRecords
{
    protected static string $resource = ConciliacaoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
