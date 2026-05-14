<?php

namespace App\Filament\Egap\Resources\Cadastro\CentroCustoResource\Pages;

use App\Filament\Egap\Resources\Cadastro\CentroCustoResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCentroCusto extends EditRecord
{
    protected static string $resource = CentroCustoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
