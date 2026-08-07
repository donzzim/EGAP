<?php

namespace App\Filament\Resources\Patrimonio\BensImoveis\TermoResponsabilidadeResource\Pages;

use Filament\Actions\DeleteAction;
use App\Filament\Resources\Patrimonio\BensImoveis\TermoResponsabilidadeResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTermoResponsabilidade extends EditRecord
{
    protected static string $resource = TermoResponsabilidadeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->label('Excluir'),
        ];
    }
}
