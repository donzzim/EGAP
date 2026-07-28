<?php

namespace App\Filament\Clusters\ExternoCluster\Almoxarifado;

class MateriaisDeConsumo extends RequisicaoMateriaisConsumoPage
{
    protected static ?int $navigationSort = 1;

    protected static ?string $navigationLabel = 'Materiais de Consumo';

    protected static ?string $title = 'Requisição de Materiais de Consumo';

    protected static string $view = 'filament.pages.externo.almoxarifado.materiais-de-consumo';

    protected function tipoMaterial(): string
    {
        return 'C';
    }
}
