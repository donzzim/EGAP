<?php

namespace App\Filament\Resources\Patrimonio\BensImoveis\TributoResource\Pages;

use App\Filament\Resources\Patrimonio\BensImoveis\TributoResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditTributo extends EditRecord
{
    protected static string $resource = TributoResource::class;

    protected ?string $heading = 'Editar Tributo';

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()->label('Excluir'),
        ];
    }
}
