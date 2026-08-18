<?php

namespace App\Mail\Patrimonio;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Confirmação enviada ao solicitante de que o pedido de inclusão de bem
 * (ver {@see SolicitacaoInclusaoBemMail}) foi recebido pela Seção de
 * Patrimônio (legado: mesmo fluxo de api/bensainventariar_operacoes.api.php).
 */
class ConfirmacaoSolicitacaoInclusaoBemMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    /**
     * @param  array<string, mixed>  $bem
     */
    public function __construct(
        public readonly array $bem,
        public readonly string $setor,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Confirmação de Solicitação no Sistema e-GAP',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.patrimonio.confirmacao-inclusao-bem',
        );
    }
}
