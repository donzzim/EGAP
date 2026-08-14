<?php

namespace App\Filament\Resources\Patrimonio\BensImoveis\DepreciacaoResource\Pages;

use Filament\Actions\CreateAction;
use App\Filament\Resources\Patrimonio\BensImoveis\DepreciacaoResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDepreciacaos extends ListRecords
{
    protected static string $resource = DepreciacaoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Novo')
                ->modalHeading('Adicionar Depreciação Imóveis')
                ->modalWidth('lg')
                ->createAnother(false),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return static::getResource()::getWidgets();
    }

    public function getWidgetData(): array
    {
        return [
            'imovelId' => $this->tableFilters['Id_imovel']['value'] ?? null,
        ];
    }
}
