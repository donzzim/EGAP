<?php

namespace App\Filament\Egap\Resources\Patrimonio\BensImoveis\BemImovelResource\Pages;

use App\Filament\Egap\Resources\Patrimonio\BensImoveis\BemImovelResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditBemImovel extends EditRecord
{
    protected static string $resource = BemImovelResource::class;

    protected ?string $heading = 'Editar Bem Imóvel';

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()->label('Excluir'),
        ];
    }
}
