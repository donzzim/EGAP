<?php

namespace App\Filament\Egap\Resources\Patrimonio\BensIntangiveis\AmortizacaoResource\Pages;

use App\Filament\Egap\Resources\Patrimonio\BensIntangiveis\AmortizacaoResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAmortizacaos extends ListRecords
{
    protected static string $resource = AmortizacaoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
