<?php

namespace App\Filament\Egap\Resources\Patrimonio\BensImoveis\ReavaliacaoResource\Pages;

use App\Filament\Egap\Resources\Patrimonio\BensImoveis\ReavaliacaoResource;
use Filament\Resources\Pages\CreateRecord;

class CreateReavaliacao extends CreateRecord
{
    protected static string $resource = ReavaliacaoResource::class;

    protected ?string $heading = 'Adicionar Reavaliação';
}
