<?php

namespace App\Filament\Egap\Resources\Patrimonio\BensMoveis\BemMovelResource\Pages;

use App\Filament\Egap\Resources\Patrimonio\BensMoveis\BemMovelResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditBemMovel extends EditRecord
{
    protected static string $resource = BemMovelResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
