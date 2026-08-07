<?php

namespace App\Filament\Resources\Cadastro\CentroCustoResource\Pages;

use App\Filament\Resources\Cadastro\CentroCustoResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditCentroCusto extends EditRecord
{
    protected static string $resource = CentroCustoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
