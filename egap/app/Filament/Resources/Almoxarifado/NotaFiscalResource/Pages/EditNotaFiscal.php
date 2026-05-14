<?php

namespace App\Filament\Egap\Resources\Almoxarifado\NotaFiscalResource\Pages;

use App\Filament\Egap\Resources\Almoxarifado\NotaFiscalResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditNotaFiscal extends EditRecord
{
    protected static string $resource = NotaFiscalResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['valor_total'] = NotaFiscalResource::calcularValorTotal($this->data['itens'] ?? []);
        $data['atualizado_por'] = auth()->id();
        $data['date_time'] = now();

        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
