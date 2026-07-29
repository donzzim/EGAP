<?php

namespace App\Filament\Clusters\ExternoCluster\Patrimonio;

use App\Filament\Clusters\ExternoCluster;
use Filament\Pages\Page;
use Filament\Pages\SubNavigationPosition;

class MovimentacaoDeMateriais extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-arrows-right-left';
    protected static ?string $cluster = ExternoCluster::class;
    protected static ?string $navigationGroup = 'Patrimônio';
    protected static ?string $navigationLabel = 'Movimentação de Materiais';
    protected static ?int $navigationSort = 2;
    protected static string $view = 'filament.pages.externo.patrimonio.movimentacao-de-materiais';

    public function getSubNavigationPosition(): SubNavigationPosition
    {
        return SubNavigationPosition::Top;
    }
}
