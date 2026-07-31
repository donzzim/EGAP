<?php

namespace App\Filament\Clusters\ExternoCluster\Patrimonio;

use App\Filament\Clusters\ExternoCluster;
use Filament\Pages\Page;
use Filament\Pages\SubNavigationPosition;
use Filament\Support\Enums\MaxWidth;

class AtividadeDeCampo extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-briefcase';
    protected static ?string $cluster = ExternoCluster::class;
    protected static ?string $slug = 'patrimonio/atividade-de-campo';
    protected static ?string $navigationGroup = 'Patrimônio';
    protected static ?string $navigationLabel = 'Ativididade de Campo';
    protected static ?string $title = 'Ativididade de Campo';
    protected static ?int $navigationSort = 6;
    protected static string $view = 'filament.pages.externo.patrimonio.atividade-de-campo';

    public function getSubNavigationPosition(): SubNavigationPosition
    {
        return SubNavigationPosition::Top;
    }

    public function getMaxContentWidth(): MaxWidth
    {
        return MaxWidth::Full;
    }
}
