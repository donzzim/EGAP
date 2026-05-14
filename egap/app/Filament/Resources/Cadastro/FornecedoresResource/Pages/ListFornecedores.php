<?php

namespace App\Filament\Egap\Resources\Cadastro\FornecedoresResource\Pages;

use App\Filament\Egap\Resources\Cadastro\FornecedoresResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListFornecedores extends ListRecords
{
    protected static string $resource = FornecedoresResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Adicionar')
                ->icon('heroicon-o-plus-circle')
                ->color('info'),
        ];
    }
}
