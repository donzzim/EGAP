<?php

namespace App\Filament\Clusters\ExternoCluster\ServicosGerais;

use BackedEnum;
use Filament\Pages\Enums\SubNavigationPosition;
use Filament\Support\Enums\Width;
use App\Filament\Clusters\ExternoCluster;
use App\Filament\Clusters\ExternoCluster\Concerns\SelecionaSetorAtual;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Livewire\Component;

class Gestor extends Page
{
    protected static ?string $cluster = ExternoCluster::class;
    protected static string|null|\UnitEnum $navigationGroup = 'Serviços Gerais';
    protected static string | BackedEnum | null $navigationIcon = Heroicon::UserGroup;
    protected static ?string $slug = 'servicos-gerais/gestores';
    protected static ?int $navigationSort = 1;
    protected static ?string $title = 'Gestores';
    protected static ?string $navigationLabel = 'Gestores';
    protected string $view = 'filament.pages.externo.servicos-gerais.gestores';

    public function tableComponent(): string
    {
        // Table qualquer
        return ExternoCluster\Livewire\Patrimonio\HistoricoDeInventarioOnlineTable::class;
    }
}
