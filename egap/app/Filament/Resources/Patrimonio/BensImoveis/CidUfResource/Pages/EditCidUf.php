<?php

namespace App\Filament\Egap\Resources\Patrimonio\BensImoveis\CidUfResource\Pages;

use App\Filament\Egap\Resources\Patrimonio\BensImoveis\CidUfResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCidUf extends EditRecord
{
    protected static string $resource = CidUfResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
