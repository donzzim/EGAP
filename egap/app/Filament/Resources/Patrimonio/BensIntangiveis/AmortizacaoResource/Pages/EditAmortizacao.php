<?php

namespace App\Filament\Resources\Patrimonio\BensIntangiveis\AmortizacaoResource\Pages;

use App\Filament\Resources\Patrimonio\BensIntangiveis\AmortizacaoResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAmortizacao extends EditRecord
{
    protected static string $resource = AmortizacaoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
