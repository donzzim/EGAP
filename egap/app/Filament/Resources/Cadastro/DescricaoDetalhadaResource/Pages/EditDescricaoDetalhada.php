<?php

namespace App\Filament\Egap\Resources\Cadastro\DescricaoDetalhadaResource\Pages;

use App\Filament\Egap\Resources\Cadastro\DescricaoDetalhadaResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDescricaoDetalhada extends EditRecord
{
    protected static string $resource = DescricaoDetalhadaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
