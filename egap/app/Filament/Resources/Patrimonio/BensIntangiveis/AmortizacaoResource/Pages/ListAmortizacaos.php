<?php

namespace App\Filament\Resources\Patrimonio\BensIntangiveis\AmortizacaoResource\Pages;

use App\Filament\Resources\Patrimonio\BensIntangiveis\AmortizacaoResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListAmortizacaos extends ListRecords
{
    protected static string $resource = AmortizacaoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Novo'),
        ];
    }
}
