<?php

namespace App\Filament\Egap\Resources\Patrimonio\BensImoveis\TipoTributoResource\Pages;

use App\Filament\Egap\Resources\Patrimonio\BensImoveis\TipoTributoResource;
use Filament\Resources\Pages\CreateRecord;

class CreateTipoTributo extends CreateRecord
{
    protected static string $resource = TipoTributoResource::class;

    protected ?string $heading = 'Adicionar Tipo de Tributo';
}
