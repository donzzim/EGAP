<?php

namespace App\Filament\Resources\Processo\MateriaisResource\Pages;

use App\Filament\Resources\Processo\MateriaisResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMateriais extends EditRecord
{
    protected static string $resource = MateriaisResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
