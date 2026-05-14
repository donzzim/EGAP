<?php

namespace App\Filament\Egap\Clusters\AdminEgapCluster\Pages;

use App\Filament\Egap\Clusters\AdminEgapCluster;
use Filament\Pages\Page;
use Filament\Pages\SubNavigationPosition;

class AcessosPermissoesPage extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-shield-check';

    protected static string $view = 'filament.egap.clusters.admin-egap-cluster.pages.acessos-permissoes-page';

    protected static ?string $title = 'Acessos e Permissões';

    protected static ?string $navigationLabel = 'Acessos e Permissões';
    protected static ?string $cluster = AdminEgapCluster::class;
    public function getSubNavigationPosition(): SubNavigationPosition
    {
        return SubNavigationPosition::Top;
    }
}
