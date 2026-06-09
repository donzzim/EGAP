<?php

namespace App\Filament\Resources\Cadastro\SetoresResource\Pages;

use App\Filament\Resources\Cadastro\SetoresResource;
use Filament\Resources\Pages\CreateRecord;

class CreateSetores extends CreateRecord
{
    protected static string $resource = SetoresResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return SetoresResource::normalizeUnidadeOrganizacionalData($data);
    }
}
