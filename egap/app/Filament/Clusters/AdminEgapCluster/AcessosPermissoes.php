<?php

namespace App\Filament\Clusters\AdminEgapCluster;

use App\Filament\Clusters\AdminEgapCluster;
use Filament\Pages\Page;
use Filament\Pages\SubNavigationPosition;

class AcessosPermissoes extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-shield-check';

    protected static string $view = 'filament.pages.admin-egap.acessos-permissoes-page';

    protected static ?string $title = 'Acessos e Permissões';

    protected static ?string $navigationLabel = 'Acessos e Permissões';
    protected static ?string $cluster = AdminEgapCluster::class;
    public function getSubNavigationPosition(): SubNavigationPosition
    {
        return SubNavigationPosition::Top;
    }
}
