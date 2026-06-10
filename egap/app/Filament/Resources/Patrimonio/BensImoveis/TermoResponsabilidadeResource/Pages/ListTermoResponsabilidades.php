<?php

namespace App\Filament\Resources\Patrimonio\BensImoveis\TermoResponsabilidadeResource\Pages;

use App\Filament\Resources\Patrimonio\BensImoveis\TermoResponsabilidadeResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTermoResponsabilidades extends ListRecords
{
    protected static string $resource = TermoResponsabilidadeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Novo')
        ];
    }
}
