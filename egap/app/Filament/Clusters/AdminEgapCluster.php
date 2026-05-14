<?php

namespace App\Filament\Egap\Clusters;

use Filament\Clusters\Cluster;
use Filament\Pages\SubNavigationPosition;

class AdminEgapCluster extends Cluster
{
    protected static ?string $navigationIcon = 'heroicon-o-wrench-screwdriver';
    protected static ?string $navigationLabel = 'Administração';

    protected static SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;

    public static function canAccess(): bool
    {
        $user = auth()->user();

        if (! $user) {
            return false;
        }

        return in_array($user->login, ['admin', 'admin2'], true);
    }
}