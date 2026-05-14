<?php

namespace App\Filament\Egap\Resources\Patrimonio\BensImoveis\EstadoConservacaoResource\Pages;

use App\Filament\Egap\Resources\Patrimonio\BensImoveis\EstadoConservacaoResource;
use Filament\Resources\Pages\CreateRecord;

class CreateEstadoConservacao extends CreateRecord
{
    protected static string $resource = EstadoConservacaoResource::class;

    protected ?string $heading = 'Adicionar Estado de Conservação';
}
