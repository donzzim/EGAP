<?php

namespace App\Filament\Resources\Admin\LotacaoResource\Pages;

use App\Filament\Resources\Admin\LotacaoResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListLotacaos extends ListRecords
{
    protected static string $resource = LotacaoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
