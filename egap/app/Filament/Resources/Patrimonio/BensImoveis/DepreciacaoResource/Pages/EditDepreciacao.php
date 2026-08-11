<?php

namespace App\Filament\Resources\Patrimonio\BensImoveis\DepreciacaoResource\Pages;

use Filament\Actions\DeleteAction;
use App\Filament\Resources\Patrimonio\BensImoveis\DepreciacaoResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDepreciacao extends EditRecord
{
    protected static string $resource = DepreciacaoResource::class;

    protected ?string $heading = 'Editar Depreciação';

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()->label('Excluir'),
        ];
    }
}
