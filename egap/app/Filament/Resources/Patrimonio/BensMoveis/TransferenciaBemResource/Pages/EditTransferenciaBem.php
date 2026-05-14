<?php

namespace App\Filament\Resources\Patrimonio\BensMoveis\TransferenciaBemResource\Pages;

use App\Filament\Resources\Patrimonio\BensMoveis\TransferenciaBemResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTransferenciaBem extends EditRecord
{
    protected static string $resource = TransferenciaBemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
