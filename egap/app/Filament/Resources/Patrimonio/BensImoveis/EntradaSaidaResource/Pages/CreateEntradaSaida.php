<?php

namespace App\Filament\Egap\Resources\Patrimonio\BensImoveis\EntradaSaidaResource\Pages;

use App\Filament\Egap\Resources\Patrimonio\BensImoveis\EntradaSaidaResource;
use Filament\Resources\Pages\CreateRecord;

class CreateEntradaSaida extends CreateRecord
{
    protected static string $resource = EntradaSaidaResource::class;

    protected ?string $heading = 'Adicionar Entrada/Saída';
}
