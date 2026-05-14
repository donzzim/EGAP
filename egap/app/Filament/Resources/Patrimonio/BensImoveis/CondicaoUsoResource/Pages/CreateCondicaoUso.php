<?php

namespace App\Filament\Resources\Patrimonio\BensImoveis\CondicaoUsoResource\Pages;

use App\Filament\Resources\Patrimonio\BensImoveis\CondicaoUsoResource;
use Filament\Resources\Pages\CreateRecord;

class CreateCondicaoUso extends CreateRecord
{
    protected static string $resource = CondicaoUsoResource::class;

    protected ?string $heading = 'Adicionar Condição de Uso';
}
