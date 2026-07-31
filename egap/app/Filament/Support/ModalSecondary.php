<?php

namespace App\Filament\Support;

use Filament\Support\Enums\MaxWidth;
use Filament\Tables\Actions\Action;

class ModalSecondary
{
    public static function apply(Action $action): Action
    {
        return $action
            ->modalWidth(MaxWidth::FourExtraLarge);
    }
}
