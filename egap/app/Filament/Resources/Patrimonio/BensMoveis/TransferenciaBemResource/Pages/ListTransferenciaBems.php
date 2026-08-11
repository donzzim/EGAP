<?php

namespace App\Filament\Resources\Patrimonio\BensMoveis\TransferenciaBemResource\Pages;

use Filament\Actions\CreateAction;
use App\Filament\Resources\Patrimonio\BensMoveis\TransferenciaBemResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTransferenciaBems extends ListRecords
{
    protected static string $resource = TransferenciaBemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Novo'),
        ];
    }
}
