<?php

namespace App\Filament\Resources\Almoxarifado\MovimentacaoEstoqueResource\Pages;

use Filament\Actions\CreateAction;
use App\Filament\Resources\Almoxarifado\MovimentacaoEstoqueResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMovimentacaoEstoques extends ListRecords
{
    protected static string $resource = MovimentacaoEstoqueResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Novo')
        ];
    }
}
