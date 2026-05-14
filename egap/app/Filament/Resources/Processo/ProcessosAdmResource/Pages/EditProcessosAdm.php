<?php

namespace App\Filament\Egap\Resources\Processo\ProcessosAdmResource\Pages;

use App\Filament\Egap\Resources\Processo\ProcessosAdmResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditProcessosAdm extends EditRecord
{
    protected static string $resource = ProcessosAdmResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
