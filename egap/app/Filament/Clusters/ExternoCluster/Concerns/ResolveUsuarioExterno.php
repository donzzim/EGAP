<?php

namespace App\Filament\Clusters\ExternoCluster\Concerns;

use App\Models\Admin\Lotacao;
use App\Models\UserEgap;

/**
 * Resolve o UserEgap (jos_users) e a lotação (setor/unidade judiciária) atuais
 * do usuário autenticado no painel, para as páginas do Ambiente Externo que
 * precisam fixar o escopo pela lotação em vez de deixá-lo selecionável.
 */
trait ResolveUsuarioExterno
{
    protected ?UserEgap $usuarioEgapCache = null;

    protected bool $usuarioEgapResolvido = false;

    protected ?Lotacao $lotacaoCache = null;

    protected bool $lotacaoResolvida = false;

    protected function usuarioEgapAtual(): ?UserEgap
    {
        if (! $this->usuarioEgapResolvido) {
            $this->usuarioEgapCache = UserEgap::currentAuthenticated();
            $this->usuarioEgapResolvido = true;
        }

        return $this->usuarioEgapCache;
    }

    protected function lotacaoAtual(): ?Lotacao
    {
        if (! $this->lotacaoResolvida) {
            $this->lotacaoCache = $this->usuarioEgapAtual()
                ?->lotacoes()
                ->with(['unidadeJudiciaria', 'setorRef'])
                ->first();
            $this->lotacaoResolvida = true;
        }

        return $this->lotacaoCache;
    }
}
