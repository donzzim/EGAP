<?php

namespace App\Filament\Egap\Resources\Patrimonio\BensImoveis\DepreciacaoResource\Pages;

use App\Filament\Egap\Resources\Patrimonio\BensImoveis\DepreciacaoResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDepreciacao extends EditRecord
{
    protected static string $resource = DepreciacaoResource::class;

    protected ?string $heading = 'Editar Depreciação';

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()->label('Excluir'),
        ];
    }
}
