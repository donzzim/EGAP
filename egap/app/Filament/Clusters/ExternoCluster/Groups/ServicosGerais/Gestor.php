<?php

namespace App\Filament\Clusters\ExternoCluster\Groups\ServicosGerais;

use App\Filament\Clusters\ExternoCluster;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;

class Gestor extends Page
{
    protected static ?string $cluster = ExternoCluster::class;
    protected static string|null|\UnitEnum $navigationGroup = 'Serviços Gerais';
    protected static string | BackedEnum | null $navigationIcon = Heroicon::UserGroup;
    protected static ?string $slug = 'servicos-gerais/gestores';
    protected static ?int $navigationSort = 1;
    protected static ?string $title = 'Gestores';
    protected static ?string $navigationLabel = 'Gestores';
    protected string $view = 'livewire.support.table-page';

    public function component(): string
    {
        // Table qualquer
        return ExternoCluster\Livewire\Patrimonio\HistoricoDeInventarioOnlineTable::class;
    }
}
