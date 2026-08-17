<?php

namespace App\Filament\Clusters\ExternoCluster\Groups\Patrimonio;

use App\Filament\Clusters\ExternoCluster;
use App\Filament\Clusters\ExternoCluster\Concerns\SelecionaSetorAtual;
use Filament\Pages\Enums\SubNavigationPosition;
use Filament\Pages\Page;
use Filament\Support\Enums\Width;

class HistoricoDeInventarioOnline extends Page
{
    use SelecionaSetorAtual;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-clock';
    protected static ?string $cluster = ExternoCluster::class;
    protected static string | \UnitEnum | null $navigationGroup = 'Patrimônio';
    protected static ?string $navigationLabel = 'Histórico de Inventário Online';
    protected static ?string $title = 'Histórico de Inventário Online';
    protected static ?string $slug = 'patrimonio/inventario-historico';
    protected static ?int $navigationSort = 5;
    protected string $view = 'livewire.support.table-page';

    public static function getSubNavigationPosition(): SubNavigationPosition
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

    public function component(): string
    {
        return ExternoCluster\Livewire\Patrimonio\HistoricoDeInventarioOnlineTable::class;
    }
}
