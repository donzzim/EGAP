<?php

namespace App\Filament\Egap\Resources\Patrimonio\BensImoveis\CondicaoUsoResource\Pages;

use App\Filament\Egap\Resources\Patrimonio\BensImoveis\CondicaoUsoResource;
use Filament\Resources\Pages\CreateRecord;

class CreateCondicaoUso extends CreateRecord
{
    protected static string $resource = CondicaoUsoResource::class;

    protected ?string $heading = 'Adicionar Condição de Uso';
}
