<?php

namespace App\Filament\Clusters\ExternoCluster\Patrimonio;

use App\Filament\Clusters\ExternoCluster;
use Filament\Pages\Page;
use Filament\Pages\SubNavigationPosition;

class RequisicaoDeMateriais extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-cube';
    protected static ?string $cluster = ExternoCluster::class;
    protected static ?string $navigationGroup = 'Patrimônio';
    protected static ?string $navigationLabel = 'Requisição de Materiais';
    protected static ?int $navigationSort = 3;
    protected static string $view = 'filament.pages.externo.patrimonio.requisicao-de-materiais';

    public function getSubNavigationPosition(): SubNavigationPosition
    {
        return SubNavigationPosition::Top;
    }
}
