<?php

namespace App\Filament\Resources\Cadastro\ContaContabilResource\Pages;

use App\Filament\Resources\Cadastro\ContaContabilResource;
use Filament\Resources\Pages\CreateRecord;

class CreateContaContabil extends CreateRecord
{
    protected static string $resource = ContaContabilResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['usuario'] = auth()->id();
        $data['date_time'] = now();

        return $data;
    }
}
