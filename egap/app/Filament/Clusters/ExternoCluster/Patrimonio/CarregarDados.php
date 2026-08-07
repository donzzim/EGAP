<?php

namespace App\Filament\Clusters\ExternoCluster\Patrimonio;

use Filament\Pages\Enums\SubNavigationPosition;
use Filament\Support\Enums\Width;
use App\Filament\Clusters\ExternoCluster;
use Filament\Pages\Page;

class CarregarDados extends Page
{
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-arrow-path-rounded-square';
    protected static ?string $cluster = ExternoCluster::class;
    protected static string | \UnitEnum | null $navigationGroup = 'Patrimônio';
    protected static ?string $navigationLabel = 'Carregar dados';
    protected static ?string $slug = 'patrimonio/carregar-dados';
    protected static ?int $navigationSort = 7;
    protected string $view = 'filament.pages.externo.patrimonio.carregar-dados';

    function getSubNavigationPosition(): SubNavigationPosition
    {
        return SubNavigationPosition::Top;
    }

    public function getMaxContentWidth(): Width
    {
        return Width::Full;
    }
}
