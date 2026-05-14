<?php

namespace App\Filament\Egap\Resources\Agendamento\SolicitacaoResource\Pages;

use App\Filament\Egap\Resources\Agendamento\AgendamentoResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSolicitacaos extends ListRecords
{
    protected static string $resource = AgendamentoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
