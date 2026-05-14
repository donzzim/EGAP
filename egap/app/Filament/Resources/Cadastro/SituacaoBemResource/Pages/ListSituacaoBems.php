<?php

namespace App\Filament\Resources\Cadastro\SituacaoBemResource\Pages;

use App\Filament\Resources\Cadastro\SituacaoBemResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSituacaoBems extends ListRecords
{
    protected static string $resource = SituacaoBemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
