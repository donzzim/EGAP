<?php

namespace App\Filament\Resources\Patrimonio\BensImoveis\TermoResponsabilidadeResource\Pages;

use App\Filament\Resources\Patrimonio\BensImoveis\TermoResponsabilidadeResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListTermoResponsabilidades extends ListRecords
{
    protected static string $resource = TermoResponsabilidadeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Novo'),
        ];
    }
}
