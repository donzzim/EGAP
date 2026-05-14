<?php

namespace App\Filament\Egap\Resources\Patrimonio\BensImoveis\BemImovelResource\Pages;

use App\Filament\Egap\Resources\Patrimonio\BensImoveis\BemImovelResource;
use Filament\Resources\Pages\CreateRecord;

class CreateBemImovel extends CreateRecord
{
    protected static string $resource = BemImovelResource::class;

    protected ?string $heading = 'Adicionar Bem Imóvel';
}
