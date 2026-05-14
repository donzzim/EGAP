<?php

namespace App\Filament\Egap\Resources\Patrimonio\BensImoveis\TermoResponsabilidadeResource\Pages;

use App\Filament\Egap\Resources\Patrimonio\BensImoveis\TermoResponsabilidadeResource;
use Filament\Resources\Pages\CreateRecord;

class CreateTermoResponsabilidade extends CreateRecord
{
    protected static string $resource = TermoResponsabilidadeResource::class;

    protected ?string $heading = 'Adicionar Termos de Responsabilidade';
}
