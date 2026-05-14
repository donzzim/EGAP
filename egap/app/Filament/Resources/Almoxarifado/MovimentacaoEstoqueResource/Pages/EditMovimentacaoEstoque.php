<?php

namespace App\Filament\Resources\Almoxarifado\MovimentacaoEstoqueResource\Pages;

use App\Filament\Resources\Almoxarifado\MovimentacaoEstoqueResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMovimentacaoEstoque extends EditRecord
{
    protected static string $resource = MovimentacaoEstoqueResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
