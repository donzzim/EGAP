<?php

namespace App\Filament\Clusters\ExternoCluster\Patrimonio;

use App\Filament\Clusters\ExternoCluster;
use App\Filament\Clusters\ExternoCluster\Concerns\SelecionaSetorAtual;
use Filament\Pages\Page;
use Filament\Pages\SubNavigationPosition;
use Filament\Support\Enums\MaxWidth;

class LevantamentoComissao extends Page
{
    use SelecionaSetorAtual;

    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $cluster = ExternoCluster::class;
    protected static ?string $navigationGroup = 'Patrimônio';
    protected static ?string $navigationLabel = 'Levantamento de Comissão';
    protected static ?string $title = 'Levantamento de Comissão';
    protected static ?string $slug = 'patrimonio/levantamento-comissao';
    protected static ?int $navigationSort = 8;
    protected static string $view = 'filament.pages.externo.patrimonio.levantamento-comissao';

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
