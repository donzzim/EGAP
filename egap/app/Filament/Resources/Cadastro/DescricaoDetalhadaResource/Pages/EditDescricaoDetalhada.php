<?php

namespace App\Filament\Resources\Cadastro\DescricaoDetalhadaResource\Pages;

use Filament\Actions\DeleteAction;
use App\Filament\Resources\Cadastro\DescricaoDetalhadaResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDescricaoDetalhada extends EditRecord
{
    protected static string $resource = DescricaoDetalhadaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
