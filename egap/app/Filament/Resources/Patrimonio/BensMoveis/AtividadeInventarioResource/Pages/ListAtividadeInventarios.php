<?php

namespace App\Filament\Egap\Resources\Patrimonio\BensMoveis\AtividadeInventarioResource\Pages;

use App\Filament\Egap\Resources\Patrimonio\BensMoveis\AtividadeInventarioResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAtividadeInventarios extends ListRecords
{
    protected static string $resource = AtividadeInventarioResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
