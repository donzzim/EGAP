<?php

namespace App\Filament\Resources\Patrimonio\BensImoveis\SituacaoResource\Pages;

use App\Filament\Resources\Patrimonio\BensImoveis\SituacaoResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSituacao extends EditRecord
{
    protected static string $resource = SituacaoResource::class;

    protected ?string $heading = 'Editar Situação';

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()->label('Excluir'),
        ];
    }
}
