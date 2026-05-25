<?php

namespace App\Filament\Resources\Patrimonio\BensImoveis\CidadesResource\Pages;

use App\Filament\Resources\Patrimonio\BensImoveis\CidadesResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCidades extends ListRecords
{
    protected static string $resource = CidadesResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Novo')
        ];
    }
}
