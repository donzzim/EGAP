<?php

namespace App\Filament\Clusters\ExternoCluster\Patrimonio;

use App\Filament\Clusters\ExternoCluster;
use App\Filament\Clusters\ExternoCluster\Patrimonio\Concerns\SelecionaSetorAtual;
use Filament\Pages\Page;
use Filament\Pages\SubNavigationPosition;
use Filament\Support\Enums\MaxWidth;

class BensNoSetor extends Page
{
    use SelecionaSetorAtual;

    protected static ?string $navigationIcon = 'heroicon-o-building-office-2';

    protected static ?string $cluster = ExternoCluster::class;

    protected static ?string $slug = 'patrimonio/inventario';

    protected static ?string $navigationGroup = 'Patrimônio';

    protected static ?string $navigationLabel = 'Bens no Setor (Inventário)';

    protected static ?int $navigationSort = 1;

    protected static string $view = 'filament.pages.externo.patrimonio.bens-no-setor';

    protected static ?string $title = 'Bens no Setor';

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
