<?php

namespace App\Filament\Resources\Patrimonio\BensImoveis\DenominacaoResource\Pages;

use App\Filament\Resources\Patrimonio\BensImoveis\DenominacaoResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDenominacao extends EditRecord
{
    protected static string $resource = DenominacaoResource::class;

    protected ?string $heading = 'Editar Denominação';

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()->label('Excluir'),
        ];
    }
}
