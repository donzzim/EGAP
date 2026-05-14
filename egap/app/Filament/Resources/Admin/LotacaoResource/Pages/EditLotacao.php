<?php

namespace App\Filament\Egap\Resources\Admin\LotacaoResource\Pages;

use App\Filament\Egap\Resources\Admin\LotacaoResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditLotacao extends EditRecord
{
    protected static string $resource = LotacaoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
