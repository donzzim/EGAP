<?php

namespace App\Filament\Resources\Patrimonio\BensImoveis\ResponsavelResource\Pages;

use App\Filament\Resources\Patrimonio\BensImoveis\ResponsavelResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditResponsavel extends EditRecord
{
    protected static string $resource = ResponsavelResource::class;

    protected ?string $heading = 'Editar Responsável';

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()->label('Excluir'),
        ];
    }
}
