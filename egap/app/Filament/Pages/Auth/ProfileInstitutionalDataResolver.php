<?php

namespace App\Filament\Pages\Auth;

use App\Models\Admin\InfoUser;
use App\Models\Admin\Lotacao;
use App\Models\UserEgap;

class ProfileInstitutionalDataResolver
{
    /**
     * @param  array<int, string>  $roleNames
     * @return array<string, string>
     */
    public static function resolve(?UserEgap $userEgap, ?InfoUser $infoUser, ?Lotacao $lotacao, array $roleNames): array
    {
        return [
            'cargo' => $infoUser?->cargo ?: '-',
            'unidade_judiciaria' => $lotacao?->unidadeJudiciaria?->Setor ?: '-',
            'setor' => $lotacao?->setorRef?->Setor ?: '-',
            'bloqueado_legado' => $userEgap === null ? '-' : ($userEgap->block ? 'Sim' : 'Não'),
            'perfis_acesso' => $roleNames === [] ? 'Nenhum perfil atribuído' : implode(', ', $roleNames),
            'data_cadastro' => $userEgap?->registerDate?->format('d/m/Y') ?? '-',
            'ultimo_acesso' => $userEgap?->lastvisitDate?->format('d/m/Y H:i') ?? '-',
        ];
    }
}
