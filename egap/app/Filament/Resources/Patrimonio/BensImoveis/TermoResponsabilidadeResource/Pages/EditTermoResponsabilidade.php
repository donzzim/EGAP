<?php

namespace App\Filament\Egap\Resources\Patrimonio\BensImoveis\TermoResponsabilidadeResource\Pages;

use App\Filament\Egap\Resources\Patrimonio\BensImoveis\TermoResponsabilidadeResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTermoResponsabilidade extends EditRecord
{
    protected static string $resource = TermoResponsabilidadeResource::class;

    protected ?string $heading = 'Editar Termos de Responsabilidade';

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()->label('Excluir'),
        ];
    }
}
