<?php

namespace App\Filament\Resources\Patrimonio\BensImoveis\EntradaSaidaResource\Pages;

use App\Filament\Resources\Patrimonio\BensImoveis\EntradaSaidaResource;
use Filament\Resources\Pages\CreateRecord;

class CreateEntradaSaida extends CreateRecord
{
    protected static string $resource = EntradaSaidaResource::class;

    protected ?string $heading = 'Adicionar Entrada/Saída';
}
