<?php

namespace App\Filament\Egap\Resources\Patrimonio\BensImoveis\CidadesResource\Pages;

use App\Filament\Egap\Resources\Patrimonio\BensImoveis\CidadesResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCidades extends ListRecords
{
    protected static string $resource = CidadesResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Adicionar')
                ->icon('heroicon-o-plus-circle')
                ->color('info'),
        ];
    }
}
