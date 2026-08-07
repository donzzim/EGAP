<?php

namespace App\Filament\Clusters\ExternoCluster\Patrimonio;

use Filament\Pages\Enums\SubNavigationPosition;
use Filament\Support\Enums\Width;
use App\Filament\Clusters\ExternoCluster;
use App\Filament\Clusters\ExternoCluster\Concerns\SelecionaSetorAtual;
use Filament\Pages\Page;

class MovimentacaoDeMateriais extends Page
{
    use SelecionaSetorAtual;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-arrows-right-left';

    protected static ?string $cluster = ExternoCluster::class;

    protected static string | \UnitEnum | null $navigationGroup = 'Patrimônio';

    protected static ?string $slug = 'patrimonio/movimentacao-de-materiais';

    protected static ?string $navigationLabel = 'Movimentação de Materiais';

    protected static ?string $title = 'Movimentação de Materiais';

    protected static ?int $navigationSort = 2;

    protected string $view = 'filament.pages.externo.patrimonio.movimentacao-de-materiais';

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
