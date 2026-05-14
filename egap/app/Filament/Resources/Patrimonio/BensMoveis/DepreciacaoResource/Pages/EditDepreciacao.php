<?php

namespace App\Filament\Resources\Patrimonio\BensMoveis\DepreciacaoResource\Pages;

use App\Filament\Resources\Patrimonio\BensMoveis\DepreciacaoResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDepreciacao extends EditRecord
{
    protected static string $resource = DepreciacaoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
