<?php

namespace App\Filament\Egap\Resources\Patrimonio\BensImoveis\DenominacaoResource\Pages;

use App\Filament\Egap\Resources\Patrimonio\BensImoveis\DenominacaoResource;
use Filament\Resources\Pages\CreateRecord;

class CreateDenominacao extends CreateRecord
{
    protected static string $resource = DenominacaoResource::class;

    protected ?string $heading = 'Adicionar Denominação';
}
