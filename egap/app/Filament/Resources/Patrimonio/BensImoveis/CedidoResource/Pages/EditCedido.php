<?php

namespace App\Filament\Resources\Patrimonio\BensImoveis\CedidoResource\Pages;

use App\Filament\Resources\Patrimonio\BensImoveis\CedidoResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditCedido extends EditRecord
{
    protected static string $resource = CedidoResource::class;

    protected ?string $heading = 'Editar Imóveis Ocupados por Terceiros';

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()->label('Excluir'),
        ];
    }
}
