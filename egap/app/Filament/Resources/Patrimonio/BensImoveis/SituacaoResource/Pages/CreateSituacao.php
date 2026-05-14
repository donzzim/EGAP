<?php

namespace App\Filament\Egap\Resources\Patrimonio\BensImoveis\SituacaoResource\Pages;

use App\Filament\Egap\Resources\Patrimonio\BensImoveis\SituacaoResource;
use Filament\Resources\Pages\CreateRecord;

class CreateSituacao extends CreateRecord
{
    protected static string $resource = SituacaoResource::class;

    protected ?string $heading = 'Adicionar Situação';
}
