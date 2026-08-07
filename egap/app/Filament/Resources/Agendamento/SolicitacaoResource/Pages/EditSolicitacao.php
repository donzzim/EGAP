<?php

namespace App\Filament\Resources\Agendamento\SolicitacaoResource\Pages;

use App\Filament\Resources\Agendamento\AgendamentoResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditSolicitacao extends EditRecord
{
    protected static string $resource = AgendamentoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
