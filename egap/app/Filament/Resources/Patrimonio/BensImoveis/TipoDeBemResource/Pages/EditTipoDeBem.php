<?php

namespace App\Filament\Resources\Patrimonio\BensImoveis\TipoDeBemResource\Pages;

use App\Filament\Resources\Patrimonio\BensImoveis\TipoDeBemResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditTipoDeBem extends EditRecord
{
    protected static string $resource = TipoDeBemResource::class;

    protected ?string $heading = 'Editar Tipo de bem';

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()->label('Excluir'),
        ];
    }
}
