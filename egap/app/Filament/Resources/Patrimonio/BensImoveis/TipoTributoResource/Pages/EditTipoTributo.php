<?php

namespace App\Filament\Resources\Patrimonio\BensImoveis\TipoTributoResource\Pages;

use App\Filament\Resources\Patrimonio\BensImoveis\TipoTributoResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditTipoTributo extends EditRecord
{
    protected static string $resource = TipoTributoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->label('Excluir'),
        ];
    }
}
