<?php

namespace App\Filament\Egap\Resources\Patrimonio\BensImoveis\BemImovelResource\Pages;

use App\Filament\Egap\Resources\Patrimonio\BensImoveis\BemImovelResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListBemImovels extends ListRecords
{
    protected static string $resource = BemImovelResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Adicionar')
                ->icon('heroicon-o-plus-circle'),
        ];
    }
}
