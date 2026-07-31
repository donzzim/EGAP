<?php

namespace App\Filament\Support;

use Filament\Support\Enums\MaxWidth;
use Filament\Tables\Actions\Action;

class ModalPrimary
{
    public static function apply(Action $action): Action
    {
        return $action
            ->modalWidth(MaxWidth::Full)
            ->extraModalWindowAttributes([
                'class' => 'egap-modal-window',
                'style' => 'width: calc(100vw - 2rem); max-width: 96rem; height: min(82dvh, 860px); overflow: hidden;',
            ])
            ->stickyModalHeader()
            ->stickyModalFooter()
            ->modalSubmitAction(false)
            ->modalCancelActionLabel('Fechar');
    }
}
