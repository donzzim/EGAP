<?php

namespace App\Filament\Resources\Patrimonio\BensImoveis\TributoResource\Pages;

use App\Filament\Resources\Patrimonio\BensImoveis\TributoResource;
use Filament\Resources\Pages\CreateRecord;

class CreateTributo extends CreateRecord
{
    protected static string $resource = TributoResource::class;

    protected ?string $heading = 'Adicionar Tributo';
}
