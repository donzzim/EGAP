<?php

namespace App\Filament\Resources\Patrimonio\BensImoveis\CondicaoUsoResource\Pages;

use App\Filament\Resources\Patrimonio\BensImoveis\CondicaoUsoResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditCondicaoUso extends EditRecord
{
    protected static string $resource = CondicaoUsoResource::class;

    protected ?string $heading = 'Editar Condição de Uso';

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()->label('Excluir'),
        ];
    }
}
