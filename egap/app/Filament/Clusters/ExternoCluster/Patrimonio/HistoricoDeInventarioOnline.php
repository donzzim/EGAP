<?php

namespace App\Filament\Clusters\ExternoCluster\Patrimonio;

use App\Filament\Clusters\ExternoCluster;
use App\Filament\Clusters\ExternoCluster\Concerns\SelecionaSetorAtual;
use Filament\Pages\Page;
use Filament\Pages\SubNavigationPosition;
use Filament\Support\Enums\MaxWidth;

class HistoricoDeInventarioOnline extends Page
{
    use SelecionaSetorAtual;

    protected static ?string $navigationIcon = 'heroicon-o-clock';
    protected static ?string $cluster = ExternoCluster::class;
    protected static ?string $navigationGroup = 'Patrimônio';
    protected static ?string $navigationLabel = 'Histórico de Inventário Online';
    protected static ?string $title = 'Histórico de Inventário Online';
    protected static ?string $slug = 'patrimonio/inventario-historico';
    protected static ?int $navigationSort = 5;
    protected static string $view = 'filament.pages.externo.patrimonio.historico-de-inventario-online';

    public function getSubNavigationPosition(): SubNavigationPosition
    {
        return SubNavigationPosition::Top;
    }

    public function getMaxContentWidth(): MaxWidth
    {
        return MaxWidth::Full;
    }

    public function getHeaderActions(): array
    {
        return [
            $this->selecionarSetorHeaderAction(),
        ];
    }
}
