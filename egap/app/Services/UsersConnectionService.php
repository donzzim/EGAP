<?php

namespace App\Services;

use App\Models\Admin\InfoUser;
use App\Models\Admin\Lotacao;
use App\Models\User;
use App\Models\UserEgap;
use App\Models\UserMobile;
use Illuminate\Database\Eloquent\Builder;

class UsersConnectionService
{
    public function findByCpf(string $cpf): ?UserMobile
    {
        $normalizedCpf = $this->normalizeCpf($cpf);

        if ($normalizedCpf === '') {
            return null;
        }

        $user = $this->findLocalUserByCpf($normalizedCpf);
        if (! $user) {
            return null;
        }

        $infoUser = $this->findInfoUserByCpf($normalizedCpf);
        if (! $infoUser) {
            return null;
        }

        return $this->buildMobileSessionUser($user, $infoUser);
    }

    public function findByUser(int|User $user): ?UserMobile
    {
        $localUser = $user instanceof User
            ? $user
            : User::query()->find($user);

        if (! $localUser) {
            return null;
        }

        if (! $localUser->cpf) {
            return null;
        }

        return $this->findByCpf((string) $localUser->cpf);
    }

    public function findByEgapUser(int|UserEgap $userEgap): ?UserMobile
    {
        $egapUser = $userEgap instanceof UserEgap
            ? $this->loadEgapUserRelations($userEgap)
            : $this->findEgapUserById($userEgap);

        if (! $egapUser) {
            return null;
        }

        $infoUser = $egapUser->infoUser;

        if (! $infoUser?->cpf) {
            return null;
        }

        $localUser = $this->findLocalUserByCpf($this->normalizeCpf((string) $infoUser->cpf));

        if (! $localUser) {
            return null;
        }

        return $this->buildMobileSessionUser($localUser, $infoUser);
    }

    public function findByLogin(string $login): ?UserMobile
    {
        $trimmedLogin = trim($login);

        if ($trimmedLogin === '') {
            return null;
        }

        $normalizedLoginCpf = $this->normalizeCpf($trimmedLogin);

        $localUser = User::query()
            ->where(function (Builder $query) use ($trimmedLogin, $normalizedLoginCpf): void {
                $query
                    ->where('login', $trimmedLogin)
                    ->orWhere('email', $trimmedLogin);

                if ($normalizedLoginCpf !== '') {
                    $query
                        ->orWhere('cpf', $normalizedLoginCpf)
                        ->orWhereRaw($this->normalizeSqlColumn('cpf').' = ?', [$normalizedLoginCpf]);
                }
            })
            ->first();

        if ($localUser?->cpf) {
            return $this->findByCpf((string) $localUser->cpf);
        }

        $egapUser = UserEgap::query()
            ->where(function (Builder $query) use ($trimmedLogin): void {
                $query
                    ->where('username', $trimmedLogin)
                    ->orWhere('email', $trimmedLogin);
            })
            ->first();

        if (! $egapUser) {
            return null;
        }

        return $this->findByEgapUser($egapUser);
    }

    private function buildMobileSessionUser(User $user, InfoUser $infoUser): UserMobile
    {
        $lotacao = $this->findLatestLotacaoByInfoUser($infoUser);

        return UserMobile::fromLinkedUsers($user, $infoUser, $lotacao);
    }

    private function findLocalUserByCpf(string $normalizedCpf): ?User
    {
        return User::query()
            ->whereNotNull('cpf')
            ->where(function (Builder $query) use ($normalizedCpf): void {
                $query
                    ->where('cpf', $normalizedCpf)
                    ->orWhereRaw($this->normalizeSqlColumn('cpf').' = ?', [$normalizedCpf]);
            })
            ->first();
    }

    private function findInfoUserByCpf(string $normalizedCpf): ?InfoUser
    {
        return InfoUser::query()
            ->whereNotNull('cpf')
            ->whereNotNull('usuario_id')
            ->where(function (Builder $query) use ($normalizedCpf): void {
                $query
                    ->where('cpf', $normalizedCpf)
                    ->orWhereRaw($this->normalizeSqlColumn('cpf').' = ?', [$normalizedCpf]);
            })
            ->orderByDesc('date_time')
            ->first();
    }

    private function findLatestLotacaoByInfoUser(InfoUser $infoUser): ?Lotacao
    {
        if (! $infoUser->usuario_id) {
            return null;
        }

        return Lotacao::query()
            ->where('id_user', (int) $infoUser->usuario_id)
            ->orderByDesc('date_time')
            ->orderByDesc('id')
            ->first();
    }

    private function findEgapUserById(int $id): ?UserEgap
    {
        $userEgap = UserEgap::query()->find($id);

        return $userEgap ? $this->loadEgapUserRelations($userEgap) : null;
    }

    private function loadEgapUserRelations(UserEgap $userEgap): UserEgap
    {
        $userEgap->loadMissing([
            'infoUser',
        ]);

        return $userEgap;
    }

    private function normalizeCpf(string $cpf): string
    {
        return preg_replace('/\D+/', '', trim($cpf)) ?? '';
    }

    private function normalizeSqlColumn(string $column): string
    {
        return "REPLACE(REPLACE(REPLACE(REPLACE(REPLACE({$column}, '.', ''), '-', ''), '/', ''), ' ', ''), ',', '')";
    }
}
