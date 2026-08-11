<?php

namespace App\Filament\Resources\Agendamento\SolicitacaoResource\Pages;

use Filament\Actions\CreateAction;
use App\Filament\Resources\Agendamento\AgendamentoResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSolicitacaos extends ListRecords
{
    protected static string $resource = AgendamentoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Novo')
        ];
    }
}
