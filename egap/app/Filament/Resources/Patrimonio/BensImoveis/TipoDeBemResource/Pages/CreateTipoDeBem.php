<?php

namespace App\Filament\Egap\Resources\Patrimonio\BensImoveis\TipoDeBemResource\Pages;

use App\Filament\Egap\Resources\Patrimonio\BensImoveis\TipoDeBemResource;
use Filament\Resources\Pages\CreateRecord;

class CreateTipoDeBem extends CreateRecord
{
    protected static string $resource = TipoDeBemResource::class;

    protected ?string $heading = 'Adicionar Tipo de bem';
}
