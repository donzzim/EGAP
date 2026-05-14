<?php

namespace App\Filament\Resources\Patrimonio\BensImoveis\ResponsavelResource\Pages;

use App\Filament\Resources\Patrimonio\BensImoveis\ResponsavelResource;
use Filament\Resources\Pages\CreateRecord;

class CreateResponsavel extends CreateRecord
{
    protected static string $resource = ResponsavelResource::class;

    protected ?string $heading = 'Adicionar Responsável';
}
