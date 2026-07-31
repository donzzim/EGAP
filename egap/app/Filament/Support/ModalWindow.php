<?php

namespace App\Filament\Support;

/**
 * Atributos HTML compartilhados pela janela dos modais padronizados (com
 * tabela ou com formulário dentro): largura cheia com scroll interno
 * controlado por CSS (ver resources/css/modal.css e a classe
 * "egap-modal-window").
 */
class ModalWindow
{
    public static function attributes(): array
    {
        return [
            'class' => 'egap-modal-window',
            'style' => 'width: calc(100vw - 2rem); max-width: 96rem; height: min(82dvh, 860px); overflow: hidden;',
        ];
    }
}
