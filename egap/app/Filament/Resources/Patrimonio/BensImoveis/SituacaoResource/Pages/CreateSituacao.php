<?php

namespace App\Filament\Resources\Patrimonio\BensImoveis\SituacaoResource\Pages;

use App\Filament\Resources\Patrimonio\BensImoveis\SituacaoResource;
use Filament\Resources\Pages\CreateRecord;

class CreateSituacao extends CreateRecord
{
    protected static string $resource = SituacaoResource::class;

    protected ?string $heading = 'Adicionar Situação';
}
