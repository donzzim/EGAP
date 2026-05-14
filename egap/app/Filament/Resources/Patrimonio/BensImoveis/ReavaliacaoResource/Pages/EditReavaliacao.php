<?php

namespace App\Filament\Resources\Patrimonio\BensImoveis\ReavaliacaoResource\Pages;

use App\Filament\Resources\Patrimonio\BensImoveis\ReavaliacaoResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditReavaliacao extends EditRecord
{
    protected static string $resource = ReavaliacaoResource::class;

    protected ?string $heading = 'Editar Reavaliação';

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()->label('Excluir'),
        ];
    }
}
