<?php

namespace App\Filament\Resources\Patrimonio\BensImoveis\BemImovelResource\Pages;

use App\Filament\Resources\Patrimonio\BensImoveis\BemImovelResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditBemImovel extends EditRecord
{
    protected static string $resource = BemImovelResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
        ];
    }
}
