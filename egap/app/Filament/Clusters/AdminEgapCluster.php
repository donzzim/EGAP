<?php

namespace App\Filament\Clusters;

use Filament\Pages\Enums\SubNavigationPosition;
use Filament\Clusters\Cluster;

class AdminEgapCluster extends Cluster
{
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-wrench-screwdriver';
    protected static ?string $navigationLabel = 'Administração';

    protected static ?\Filament\Pages\Enums\SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;

    public static function canAccess(): bool
    {
        $user = auth()->user();

        if (! $user) {
            return false;
        }

        return in_array($user->login, ['admin', 'admin2'], true);
    }
}
