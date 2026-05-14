<?php

namespace App\Filament\Egap\Resources\Patrimonio\BensImoveis\DepreciacaoResource\Pages;

use App\Filament\Egap\Resources\Patrimonio\BensImoveis\DepreciacaoResource;
use Filament\Resources\Pages\CreateRecord;

class CreateDepreciacao extends CreateRecord
{
    protected static string $resource = DepreciacaoResource::class;

    protected ?string $heading = 'Adicionar Depreciação';
}
