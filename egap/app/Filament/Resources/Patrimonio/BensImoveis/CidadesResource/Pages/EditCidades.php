<?php

namespace App\Filament\Resources\Patrimonio\BensImoveis\CidadesResource\Pages;

use Filament\Actions\DeleteAction;
use App\Filament\Resources\Patrimonio\BensImoveis\CidadesResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCidades extends EditRecord
{
    protected static string $resource = CidadesResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
