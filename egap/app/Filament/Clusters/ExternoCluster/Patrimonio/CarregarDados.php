<?php

namespace App\Filament\Clusters\ExternoCluster\Patrimonio;

use App\Filament\Clusters\ExternoCluster;
use Filament\Pages\Page;
use Filament\Pages\SubNavigationPosition;

class CarregarDados extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-arrow-path-rounded-square';
    protected static ?string $cluster = ExternoCluster::class;
    protected static ?string $navigationGroup = 'Patrimônio';
    protected static ?string $navigationLabel = 'Carregar dados';
    protected static ?int $navigationSort = 7;
    protected static string $view = 'filament.pages.externo.patrimonio.carregar-dados';

    public function getSubNavigationPosition(): SubNavigationPosition
    {
        return SubNavigationPosition::Top;
    }
}
