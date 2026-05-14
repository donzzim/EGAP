<?php

namespace App\Filament\Egap\Resources\Patrimonio\BensImoveis\ResponsavelResource\Pages;

use App\Filament\Egap\Resources\Patrimonio\BensImoveis\ResponsavelResource;
use Filament\Resources\Pages\CreateRecord;

class CreateResponsavel extends CreateRecord
{
    protected static string $resource = ResponsavelResource::class;

    protected ?string $heading = 'Adicionar Responsável';
}
