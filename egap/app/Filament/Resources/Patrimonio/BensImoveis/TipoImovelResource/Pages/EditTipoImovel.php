<?php

namespace App\Filament\Egap\Resources\Patrimonio\BensImoveis\TipoImovelResource\Pages;

use App\Filament\Egap\Resources\Patrimonio\BensImoveis\TipoImovelResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTipoImovel extends EditRecord
{
    protected static string $resource = TipoImovelResource::class;

    protected ?string $heading = 'Editar Tipo de Imóvel';

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()->label('Excluir'),
        ];
    }
}
