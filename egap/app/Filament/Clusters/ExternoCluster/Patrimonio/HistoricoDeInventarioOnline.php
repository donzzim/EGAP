<?php

namespace App\Filament\Clusters\ExternoCluster\Patrimonio;

use App\Filament\Clusters\ExternoCluster;
use Filament\Pages\Page;
use Filament\Pages\SubNavigationPosition;

class HistoricoDeInventarioOnline extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-clock';
    protected static ?string $cluster = ExternoCluster::class;
    protected static ?string $navigationGroup = 'Patrimônio';
    protected static ?string $navigationLabel = 'Histórico de Inventário Online';
    protected static ?int $navigationSort = 5;
    protected static string $view = 'filament.pages.externo.patrimonio.historico-de-inventario-online';

    public function getSubNavigationPosition(): SubNavigationPosition
    {
        return SubNavigationPosition::Top;
    }
}
