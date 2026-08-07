<?php

namespace App\Filament\Resources\Cadastro\FornecedoresResource\Pages;

use App\Filament\Resources\Cadastro\FornecedoresResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListFornecedores extends ListRecords
{
    protected static string $resource = FornecedoresResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Novo'),
        ];
    }
}
