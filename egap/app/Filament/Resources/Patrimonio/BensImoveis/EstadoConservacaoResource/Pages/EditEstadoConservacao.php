<?php

namespace App\Filament\Egap\Resources\Patrimonio\BensImoveis\EstadoConservacaoResource\Pages;

use App\Filament\Egap\Resources\Patrimonio\BensImoveis\EstadoConservacaoResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditEstadoConservacao extends EditRecord
{
    protected static string $resource = EstadoConservacaoResource::class;

    protected ?string $heading = 'Editar Estado de Conservação';

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()->label('Excluir'),
        ];
    }
}
