<?php

namespace App\Filament\Resources\Patrimonio\BensImoveis\ObraResource\Pages;

use Filament\Actions\DeleteAction;
use App\Filament\Resources\Patrimonio\BensImoveis\ObraResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditObra extends EditRecord
{
    protected static string $resource = ObraResource::class;

    protected ?string $heading = 'Editar Obras e Ampliações';

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()->label('Excluir'),
        ];
    }
}
