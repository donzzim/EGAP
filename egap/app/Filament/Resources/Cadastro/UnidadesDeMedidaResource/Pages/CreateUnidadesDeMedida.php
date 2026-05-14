<?php

namespace App\Filament\Resources\Cadastro\UnidadesDeMedidaResource\Pages;

use App\Filament\Resources\Cadastro\UnidadesDeMedidaResource;
use Filament\Resources\Pages\CreateRecord;

class CreateUnidadesDeMedida extends CreateRecord
{
    protected static string $resource = UnidadesDeMedidaResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['Usuario'] = auth()->id();
        $data['date_time'] = now();

        return $data;
    }
    protected function getCreateFormActionLabel(): string
    {
        return 'Salvar';
    }

    protected function getCreateAnotherFormActionLabel(): string
    {
        return 'Salvar e criar outro';
    }

    protected function getCancelFormActionLabel(): string
    {
        return 'Cancelar';
    }
}
