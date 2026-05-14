<?php

namespace App\Filament\Egap\Resources\Cadastro\UnidadesDeMedidaResource\Pages;

use App\Filament\Egap\Resources\Cadastro\UnidadesDeMedidaResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditUnidadesDeMedida extends EditRecord
{
    protected static string $resource = UnidadesDeMedidaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function getSaveFormActionLabel(): string
    {
        return 'Salvar Alterações';
    }

    protected function getCancelFormActionLabel(): string
    {
        return 'Cancelar';
    }
}
