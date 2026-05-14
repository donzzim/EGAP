<?php

namespace App\Filament\Egap\Resources\Patrimonio\BensImoveis\CedidoResource\Pages;

use App\Filament\Egap\Resources\Patrimonio\BensImoveis\CedidoResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCedido extends EditRecord
{
    protected static string $resource = CedidoResource::class;

    protected ?string $heading = 'Editar Imóveis Ocupados por Terceiros';

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()->label('Excluir'),
        ];
    }
}
