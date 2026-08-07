<?php

namespace App\Filament\Clusters\ExternoCluster\Patrimonio;

use Filament\Pages\Enums\SubNavigationPosition;
use Filament\Support\Enums\Width;
use App\Filament\Clusters\ExternoCluster;
use App\Filament\Clusters\ExternoCluster\Concerns\SelecionaSetorAtual;
use Filament\Pages\Page;

class LevantamentoComissao extends Page
{
    use SelecionaSetorAtual;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-users';
    protected static ?string $cluster = ExternoCluster::class;
    protected static string | \UnitEnum | null $navigationGroup = 'Patrimônio';
    protected static ?string $navigationLabel = 'Levantamento de Comissão';
    protected static ?string $title = 'Levantamento de Comissão';
    protected static ?string $slug = 'patrimonio/levantamento-comissao';
    protected static ?int $navigationSort = 8;
    protected string $view = 'filament.pages.externo.patrimonio.levantamento-comissao';

    function getSubNavigationPosition(): SubNavigationPosition
    {
        return SubNavigationPosition::Top;
    }

    public function getMaxContentWidth(): Width
    {
        return Width::Full;
    }

    public function getHeaderActions(): array
    {
        return [
            $this->selecionarSetorHeaderAction(),
        ];
    }
}
