<?php

namespace App\Filament\Resources\Admin\LotacaoResource\Pages;

use Filament\Actions\DeleteAction;
use App\Filament\Resources\Admin\LotacaoResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditLotacao extends EditRecord
{
    protected static string $resource = LotacaoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
