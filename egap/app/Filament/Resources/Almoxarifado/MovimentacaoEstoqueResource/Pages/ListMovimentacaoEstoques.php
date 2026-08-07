<?php

namespace App\Filament\Resources\Almoxarifado\MovimentacaoEstoqueResource\Pages;

use App\Filament\Resources\Almoxarifado\MovimentacaoEstoqueResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListMovimentacaoEstoques extends ListRecords
{
    protected static string $resource = MovimentacaoEstoqueResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Novo'),
        ];
    }
}
