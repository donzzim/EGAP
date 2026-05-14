<?php

namespace App\Filament\Resources\Patrimonio\BensImoveis\CedidoResource\Pages;

use App\Filament\Resources\Patrimonio\BensImoveis\CedidoResource;
use Filament\Resources\Pages\CreateRecord;

class CreateCedido extends CreateRecord
{
    protected static string $resource = CedidoResource::class;

    protected ?string $heading = 'Adicionar Imóveis Ocupados por Terceiros';
}
