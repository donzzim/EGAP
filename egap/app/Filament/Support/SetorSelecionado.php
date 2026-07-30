<?php

namespace App\Filament\Support;

use App\Models\UserEgap;

/**
 * Resolve e armazena, em sessão, o setor que o usuário do Ambiente Externo está
 * visualizando nas páginas do cluster de Patrimônio (Bens no Setor, Movimentação
 * de Materiais, etc).
 *
 * Solução provisória enquanto a migração de usuários/sessão do legado não está
 * pronta: sem o vínculo confiável de setor (sessão do sistema antigo), pedimos a
 * identificação manual do setor. Deve ser removida quando o setor puder ser
 * resolvido automaticamente a partir do usuário autenticado.
 */
class SetorSelecionado
{
    public const SESSION_KEY = 'externo.patrimonio.setor_selecionado';

    public static function resolverAtual(): ?int
    {
        $setorSessao = session(self::SESSION_KEY.'.setor');

        if (filled($setorSessao)) {
            return (int) $setorSessao;
        }

        return UserEgap::currentAuthenticated()
            ?->lotacoes()
            ->first()
            ?->setor;
    }
}
