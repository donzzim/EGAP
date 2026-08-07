<?php

namespace App\Filament\Resources\Patrimonio\BensImoveis\CidUfResource\Pages;

use Filament\Actions\DeleteAction;
use App\Filament\Resources\Patrimonio\BensImoveis\CidUfResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCidUf extends EditRecord
{
    protected static string $resource = CidUfResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
