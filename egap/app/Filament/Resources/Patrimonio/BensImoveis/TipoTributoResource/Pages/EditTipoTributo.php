<?php

namespace App\Filament\Egap\Resources\Patrimonio\BensImoveis\TipoTributoResource\Pages;

use App\Filament\Egap\Resources\Patrimonio\BensImoveis\TipoTributoResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTipoTributo extends EditRecord
{
    protected static string $resource = TipoTributoResource::class;

    protected ?string $heading = 'Editar Tipo de Tributo';

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()->label('Excluir'),
        ];
    }
}
