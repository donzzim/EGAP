<?php

namespace App\Filament\Clusters\ExternoCluster\Patrimonio;

use App\Filament\Clusters\ExternoCluster;
use Filament\Pages\Page;
use Filament\Pages\SubNavigationPosition;

class BensNoSetor extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-building-office-2';
    protected static ?string $cluster = ExternoCluster::class;
    protected static ?string $navigationGroup = 'Patrimônio';
    protected static ?string $navigationLabel = 'Bens no Setor (Inventário)';
    protected static ?int $navigationSort = 1;
    protected static string $view = 'filament.pages.externo.patrimonio.bens-no-setor';

    public function getSubNavigationPosition(): SubNavigationPosition
    {
        return SubNavigationPosition::Top;
    }
}
