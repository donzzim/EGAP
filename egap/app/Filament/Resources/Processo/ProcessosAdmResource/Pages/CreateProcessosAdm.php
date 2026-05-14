<?php

namespace App\Filament\Egap\Resources\Processo\ProcessosAdmResource\Pages;

use App\Filament\Egap\Resources\Processo\ProcessosAdmResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateProcessosAdm extends CreateRecord
{
    protected static string $resource = ProcessosAdmResource::class;

    protected ?string $heading = 'Adicionar Processo';
}
