<?php

namespace App\Filament\Egap\Resources\Patrimonio\BensImoveis\TipoImovelResource\Pages;

use App\Filament\Egap\Resources\Patrimonio\BensImoveis\TipoImovelResource;
use Filament\Resources\Pages\CreateRecord;

class CreateTipoImovel extends CreateRecord
{
    protected static string $resource = TipoImovelResource::class;

    protected ?string $heading = 'Adicionar Tipo de Imóvel';
}
