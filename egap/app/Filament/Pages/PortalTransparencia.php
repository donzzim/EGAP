<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class PortalTransparencia extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-presentation-chart-line';

    protected static ?string $navigationLabel = 'Portal Transparência';

    protected static ?int $navigationSort = 10;

    protected static ?string $title = 'Portal Transparência';

    protected static string $view = 'filament.pages.portal-transparencia';
}
