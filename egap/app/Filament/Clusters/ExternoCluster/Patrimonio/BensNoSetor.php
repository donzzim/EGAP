<?php

namespace App\Filament\Clusters\ExternoCluster\Patrimonio;

use App\Filament\Clusters\ExternoCluster;
use App\Filament\Clusters\ExternoCluster\Concerns\SelecionaSetorAtual;
use Filament\Pages\Enums\SubNavigationPosition;
use Filament\Pages\Page;
use Filament\Support\Enums\Width;

class BensNoSetor extends Page
{
    use SelecionaSetorAtual;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-building-office-2';

    protected static ?string $cluster = ExternoCluster::class;

    protected static ?string $slug = 'patrimonio/inventario';

    protected static string|\UnitEnum|null $navigationGroup = 'Patrimônio';

    protected static ?string $navigationLabel = 'Bens no Setor (Inventário)';

    protected static ?int $navigationSort = 1;

    protected string $view = 'filament.pages.externo.patrimonio.bens-no-setor';

    protected static ?string $title = 'Bens no Setor';

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
}
