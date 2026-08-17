<?php

namespace App\Filament\Support;

use App\Models\UserEgap;

/**
 * Resolve e armazena, em sessão, o usuário (jos_users) que o Ambiente Externo
 * está tratando como solicitante nas páginas do cluster de Agendamento.
 *
 * Solução provisória enquanto a migração de usuários/sessão do legado não está
 * pronta: sem o vínculo confiável entre o usuário autenticado no painel e o seu
 * registro em jos_users (sessão do sistema antigo), pedimos a identificação
 * manual do usuário. Deve ser removida quando o usuário puder ser resolvido
 * automaticamente a partir da autenticação. Mesmo padrão de {@see SetorSelecionado}.
 */
class UsuarioSelecionado
{
    public const SESSION_KEY = 'externo.agendamento.usuario_selecionado';

    public static function resolverAtual(): ?int
    {
        $usuarioSessao = session(self::SESSION_KEY);

        if (filled($usuarioSessao)) {
            return (int) $usuarioSessao;
        }

        return UserEgap::currentAuthenticated()?->id;
    }
}
