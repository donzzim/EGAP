<?php

namespace App\Filament\Resources\Patrimonio\BensImoveis\ReavaliacaoResource\Pages;

use Filament\Actions\DeleteAction;
use App\Filament\Resources\Patrimonio\BensImoveis\ReavaliacaoResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditReavaliacao extends EditRecord
{
    protected static string $resource = ReavaliacaoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()->label('Excluir'),
        ];
    }
}
