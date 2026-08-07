<?php

namespace App\Filament\Resources\Processo\ProcessosAdmResource\Pages;

use App\Filament\Resources\Processo\ProcessosAdmResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditProcessosAdm extends EditRecord
{
    protected static string $resource = ProcessosAdmResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
