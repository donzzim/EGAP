<?php

namespace App\Filament\Support;

use Filament\Actions\Action;

/**
 * Padroniza as Actions que abrem um modal contendo uma tabela (ex.: "Ver itens",
 * "Histórico de Movimentações"): largura cheia, cabeçalho/rodapé fixos, sem botão
 * de submit e com scroll interno da tabela habilitado via classe "egap-modal-window"
 * (ver resources/css/modal.css). O conteúdo do modal deve renderizar a view
 * apontada por {@see self::VIEW}, que já aplica a classe "egap-modal-content"
 * necessária para o scroll funcionar.
 */
class TableModalAction
{
    public const VIEW = 'livewire.support.table-modal';

    public static function make(string $name): Action
    {
        return Action::make($name)
            ->modalWidth('full')
            ->extraModalWindowAttributes(ModalWindow::attributes())
            ->stickyModalHeader()
            ->stickyModalFooter()
            ->modalSubmitAction(false)
            ->modalCancelActionLabel('Fechar');
    }
}
