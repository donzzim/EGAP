<?php

namespace App\Mail\Patrimonio;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * E-mail enviado à Seção de Patrimônio solicitando a inclusão de um bem ainda
 * não cadastrado no setor (legado: bens.php modal "Incluir Bem" +
 * api/bensainventariar_operacoes.api.php, $_POST['incluir_pat']).
 *
 * Simplificado do legado: um bem por solicitação, em vez do cadastro em lote.
 */
class SolicitacaoInclusaoBemMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    /**
     * @param  array<string, mixed>  $bem
     */
    public function __construct(
        public readonly array $bem,
        public readonly string $solicitante,
        public readonly string $setor,
        public readonly ?string $emailSolicitante = null,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Solicitação de Inclusão de Bem no Sistema e-GAP',
            replyTo: $this->emailSolicitante ? [$this->emailSolicitante] : [],
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.patrimonio.solicitacao-inclusao-bem',
        );
    }
}
