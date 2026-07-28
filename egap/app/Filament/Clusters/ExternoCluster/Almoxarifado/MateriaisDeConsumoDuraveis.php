<?php

namespace App\Filament\Clusters\ExternoCluster\Almoxarifado;

class MateriaisDeConsumoDuraveis extends RequisicaoMateriaisConsumoPage
{
    protected static ?int $navigationSort = 2;

    protected static ?string $navigationLabel = 'Materiais de Cons. Duráveis';

    protected static ?string $title = 'Requisição de Materiais de Consumo Duráveis';

    protected static string $view = 'filament.pages.externo.almoxarifado.materiais-de-consumo-duraveis';

    protected function tipoMaterial(): string
    {
        return 'D';
    }
}
