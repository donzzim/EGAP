<?php

namespace App\Filament\Clusters\AdminEgapCluster;

use Filament\Pages\Enums\SubNavigationPosition;
use App\Filament\Clusters\AdminEgapCluster;
use Filament\Pages\Page;

class AcessosPermissoes extends Page
{
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-shield-check';

    protected string $view = 'filament.pages.admin-egap.acessos-permissoes-page';

    protected static ?string $title = 'Acessos e Permissões';

    protected static ?string $navigationLabel = 'Acessos e Permissões';
    protected static ?string $cluster = AdminEgapCluster::class;
    function getSubNavigationPosition(): SubNavigationPosition
    {
        return SubNavigationPosition::Top;
    }
}
