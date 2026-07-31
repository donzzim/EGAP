<?php

namespace App\Filament\Clusters\ExternoCluster\Patrimonio;

use App\Filament\Clusters\ExternoCluster;
use App\Filament\Clusters\ExternoCluster\Patrimonio\Concerns\SelecionaSetorAtual;
use Filament\Pages\Page;
use Filament\Pages\SubNavigationPosition;
use Filament\Support\Enums\MaxWidth;

class MovimentacaoDeMateriais extends Page
{
    use SelecionaSetorAtual;

    protected static ?string $navigationIcon = 'heroicon-o-arrows-right-left';

    protected static ?string $cluster = ExternoCluster::class;

    protected static ?string $navigationGroup = 'Patrimônio';

    protected static ?string $slug = 'patrimonio/movimentacao-de-materiais';

    protected static ?string $navigationLabel = 'Movimentação de Materiais';

    protected static ?string $title = 'Movimentação de Materiais';

    protected static ?int $navigationSort = 2;

    protected static string $view = 'filament.pages.externo.patrimonio.movimentacao-de-materiais';

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
