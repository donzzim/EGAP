<?php

namespace App\Filament\Resources\Patrimonio\BensImoveis\CidadesResource\Pages;

use App\Filament\Resources\Patrimonio\BensImoveis\CidadesResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCidades extends EditRecord
{
    protected static string $resource = CidadesResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
