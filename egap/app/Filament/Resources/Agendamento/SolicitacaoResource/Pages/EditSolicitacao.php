<?php

namespace App\Filament\Egap\Resources\Agendamento\SolicitacaoResource\Pages;

use App\Filament\Egap\Resources\Agendamento\AgendamentoResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSolicitacao extends EditRecord
{
    protected static string $resource = AgendamentoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
