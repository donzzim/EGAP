<?php

namespace App\Filament\Resources\Cadastro\ContaContabilResource\Pages;

use App\Filament\Resources\Cadastro\ContaContabilResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListContaContabils extends ListRecords
{
    protected static string $resource = ContaContabilResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Novo'),
        ];
    }
}
