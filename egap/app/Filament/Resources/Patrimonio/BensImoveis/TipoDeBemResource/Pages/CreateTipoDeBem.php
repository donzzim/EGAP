<?php

namespace App\Filament\Resources\Patrimonio\BensImoveis\TipoDeBemResource\Pages;

use App\Filament\Resources\Patrimonio\BensImoveis\TipoDeBemResource;
use Filament\Resources\Pages\CreateRecord;

class CreateTipoDeBem extends CreateRecord
{
    protected static string $resource = TipoDeBemResource::class;

    protected ?string $heading = 'Adicionar Tipo de bem';
}
