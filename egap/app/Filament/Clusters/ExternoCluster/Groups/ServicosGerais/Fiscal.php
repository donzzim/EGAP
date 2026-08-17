<?php

namespace App\Filament\Clusters\ExternoCluster\Groups\ServicosGerais;

use App\Filament\Clusters\ExternoCluster;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;

class Fiscal extends Page
{
    protected static ?string $cluster = ExternoCluster::class;
    protected static string|null|\UnitEnum $navigationGroup = 'Serviços Gerais';
    protected static string | BackedEnum | null $navigationIcon = Heroicon::ShieldCheck;
    protected static ?string $slug = 'servicos-gerais/fiscais';
    protected static ?int $navigationSort = 2;
    protected static ?string $title = 'Fiscais';
    protected static ?string $navigationLabel = 'Fiscais';
    protected string $view = 'livewire.support.table-page';

    public function component(): string
    {
        // Table qualquer
        return ExternoCluster\Livewire\Patrimonio\HistoricoDeInventarioOnlineTable::class;
    }
}
