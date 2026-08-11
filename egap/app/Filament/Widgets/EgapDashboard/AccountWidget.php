<?php

namespace App\Filament\Widgets\EgapDashboard;

use Filament\Widgets\Widget;

class AccountWidget extends Widget
{
    protected static ?int $sort = -3;

    protected static bool $isLazy = false;

    protected string $view = 'filament.widgets.account-widget';

    protected int | string | array $columnSpan = 'full';
}
