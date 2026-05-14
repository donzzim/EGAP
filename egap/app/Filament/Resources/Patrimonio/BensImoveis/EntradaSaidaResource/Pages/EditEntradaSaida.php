<?php

namespace App\Filament\Resources\Patrimonio\BensImoveis\EntradaSaidaResource\Pages;

use App\Filament\Resources\Patrimonio\BensImoveis\EntradaSaidaResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditEntradaSaida extends EditRecord
{
    protected static string $resource = EntradaSaidaResource::class;

    protected ?string $heading = 'Editar Entrada/Saída';

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()->label('Excluir'),
        ];
    }
}
