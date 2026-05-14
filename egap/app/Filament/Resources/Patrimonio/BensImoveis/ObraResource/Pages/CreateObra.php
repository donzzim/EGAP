<?php

namespace App\Filament\Egap\Resources\Patrimonio\BensImoveis\ObraResource\Pages;

use App\Filament\Egap\Resources\Patrimonio\BensImoveis\ObraResource;
use Filament\Resources\Pages\CreateRecord;

class CreateObra extends CreateRecord
{
    protected static string $resource = ObraResource::class;

    protected ?string $heading = 'Adicionar Obras e Ampliações';
}
