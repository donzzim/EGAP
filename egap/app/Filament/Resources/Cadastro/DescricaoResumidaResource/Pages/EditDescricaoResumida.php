<?php

namespace App\Filament\Egap\Resources\Cadastro\DescricaoResumidaResource\Pages;

use App\Filament\Egap\Resources\Cadastro\DescricaoResumidaResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDescricaoResumida extends EditRecord
{
    protected static string $resource = DescricaoResumidaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
