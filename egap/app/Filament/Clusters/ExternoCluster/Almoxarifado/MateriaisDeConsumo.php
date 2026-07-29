<?php

namespace App\Filament\Clusters\ExternoCluster\Almoxarifado;

use App\Filament\Clusters\ExternoCluster;

class MateriaisDeConsumo extends RequisicaoDeMateriaisPage
{
    protected static ?int $navigationSort = 1;

    protected static ?string $cluster = ExternoCluster::class;

    protected static ?string $navigationIcon = 'heroicon-o-inbox';

    protected static ?string $navigationGroup = 'Almoxarifado';

    protected static ?string $navigationLabel = 'Materiais de Consumo';

    protected static ?string $title = 'Requisição de Materiais de Consumo';

    protected static string $view = 'filament.pages.externo.almoxarifado.materiais-de-consumo';

    public function tipoMaterial(): string
    {
        return 'C';
    }
}
