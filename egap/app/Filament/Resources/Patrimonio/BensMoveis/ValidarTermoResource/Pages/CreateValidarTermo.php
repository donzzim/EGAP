<?php

namespace App\Filament\Resources\Patrimonio\BensMoveis\ValidarTermoResource\Pages;

use App\Filament\Resources\Patrimonio\BensMoveis\ValidarTermoResource;
use Filament\Resources\Pages\CreateRecord;

class CreateValidarTermo extends CreateRecord
{
    protected static string $resource = ValidarTermoResource::class;

    protected static ?string $title = 'Anexos do Termo';
}
