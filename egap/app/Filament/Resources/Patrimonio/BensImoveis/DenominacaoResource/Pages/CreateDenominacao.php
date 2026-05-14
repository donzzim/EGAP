<?php

namespace App\Filament\Resources\Patrimonio\BensImoveis\DenominacaoResource\Pages;

use App\Filament\Resources\Patrimonio\BensImoveis\DenominacaoResource;
use Filament\Resources\Pages\CreateRecord;

class CreateDenominacao extends CreateRecord
{
    protected static string $resource = DenominacaoResource::class;

    protected ?string $heading = 'Adicionar Denominação';
}
