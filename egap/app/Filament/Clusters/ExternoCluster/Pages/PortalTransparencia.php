<?php

namespace App\Filament\Clusters\ExternoCluster\Pages;

use App\Filament\Clusters\ExternoCluster;
use Filament\Pages\Page;

class PortalTransparencia extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-presentation-chart-line';

    protected static ?string $navigationLabel = 'Portal Transparência';

    protected static ?int $navigationSort = 100;

    protected static ?string $cluster = ExternoCluster::class;

    protected static ?string $title = 'Portal Transparência';

    protected string $view = 'filament.pages.externo.portal-transparencia';
}
