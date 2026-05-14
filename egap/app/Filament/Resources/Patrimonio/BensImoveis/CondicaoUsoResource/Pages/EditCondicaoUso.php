<?php

namespace App\Filament\Egap\Resources\Patrimonio\BensImoveis\CondicaoUsoResource\Pages;

use App\Filament\Egap\Resources\Patrimonio\BensImoveis\CondicaoUsoResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCondicaoUso extends EditRecord
{
    protected static string $resource = CondicaoUsoResource::class;

    protected ?string $heading = 'Editar Condição de Uso';

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()->label('Excluir'),
        ];
    }
}
