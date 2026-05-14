<?php

namespace App\Filament\Egap\Widgets\EgapDashboard;

use Filament\Widgets\Widget;

class AccountWidget extends Widget
{
    protected static ?int $sort = -3;

    protected static bool $isLazy = false;

    protected static string $view = 'filament.widgets.account-widget';

    protected int | string | array $columnSpan = 'full';
}
