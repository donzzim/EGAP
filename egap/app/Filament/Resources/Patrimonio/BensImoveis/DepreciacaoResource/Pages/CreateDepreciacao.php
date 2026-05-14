<?php

namespace App\Filament\Resources\Patrimonio\BensImoveis\DepreciacaoResource\Pages;

use App\Filament\Resources\Patrimonio\BensImoveis\DepreciacaoResource;
use Filament\Resources\Pages\CreateRecord;

class CreateDepreciacao extends CreateRecord
{
    protected static string $resource = DepreciacaoResource::class;

    protected ?string $heading = 'Adicionar Depreciação';
}
