<?php

namespace App\Filament\Egap\Resources\Patrimonio\BensImoveis\ObraResource\Pages;

use App\Filament\Egap\Resources\Patrimonio\BensImoveis\ObraResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditObra extends EditRecord
{
    protected static string $resource = ObraResource::class;

    protected ?string $heading = 'Editar Obras e Ampliações';

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()->label('Excluir'),
        ];
    }
}
