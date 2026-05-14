<?php

namespace App\Filament\Resources\Patrimonio\BensImoveis\TipoTributoResource\Pages;

use App\Filament\Resources\Patrimonio\BensImoveis\TipoTributoResource;
use Filament\Resources\Pages\CreateRecord;

class CreateTipoTributo extends CreateRecord
{
    protected static string $resource = TipoTributoResource::class;

    protected ?string $heading = 'Adicionar Tipo de Tributo';
}
