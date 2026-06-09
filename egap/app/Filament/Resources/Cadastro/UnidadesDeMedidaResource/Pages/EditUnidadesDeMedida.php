<?php

namespace App\Filament\Resources\Cadastro\UnidadesDeMedidaResource\Pages;

use App\Filament\Resources\Cadastro\UnidadesDeMedidaResource;
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
}
