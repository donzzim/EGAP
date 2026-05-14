<?php

namespace App\Filament\Resources\Patrimonio\BensMoveis\BaixaResource\Pages;

use App\Filament\Resources\Patrimonio\BensMoveis\BaixaResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListBaixas extends ListRecords
{
    protected static string $resource = BaixaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
