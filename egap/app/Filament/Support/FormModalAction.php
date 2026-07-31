<?php

namespace App\Filament\Support;

use Filament\Tables\Actions\Action;

/**
 * Padroniza as Actions que abrem um modal contendo um formulário próprio
 * (ex.: "Transferir Bens"): mesmo "chrome" visual dos modais com tabela
 * (largura cheia, cabeçalho fixo, janela com scroll interno via classe
 * "egap-modal-window"), mas com o rodapé e os botões de ação desabilitados
 * no Filament, pois quem os renderiza é o próprio componente Livewire
 * (ver {@see FormModalComponent}), livre para desenhar o formulário
 * como preferir.
 */
class FormModalAction
{
    public const VIEW = 'livewire.support.form-modal';

    public static function make(string $name): Action
    {
        return Action::make($name)
            ->modalWidth('full')
            ->extraModalWindowAttributes(ModalWindow::attributes())
            ->stickyModalHeader()
            ->modalSubmitAction(false)
            ->modalCancelAction(false);
    }
}
