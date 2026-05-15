<?php

namespace App\Services;

use App\Models\Admin\InfoUser;
use App\Models\User;
use App\Models\UserEgap;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Fluent;

class UsersConnectionService
{
    public function findByCpf(string $cpf): ?Fluent
    {
        $normalizedCpf = $this->normalizeCpf($cpf);

        if ($normalizedCpf === '') {
            return null;
        }

        $user = $this->findLocalUserByCpf($normalizedCpf);
        $infoUser = $this->findInfoUserByCpf($normalizedCpf);
        $userEgap = $infoUser?->usuario_id
            ? $this->findEgapUserById((int) $infoUser->usuario_id)
            : null;

        return $this->buildConnectedUser($user, $infoUser, $userEgap);
    }

    public function findByUser(int|User $user): ?Fluent
    {
        $localUser = $user instanceof User
            ? $user
            : User::query()->find($user);

        if (! $localUser) {
            return null;
        }

        return $this->findByCpf((string) $localUser->cpf);
    }

    public function findByEgapUser(int|UserEgap $userEgap): ?Fluent
    {
        $egapUser = $userEgap instanceof UserEgap
            ? $this->loadEgapUserRelations($userEgap)
            : $this->findEgapUserById($userEgap);

        if (! $egapUser) {
            return null;
        }

        $infoUser = $egapUser->infoUser;

        if (! $infoUser?->cpf) {
            return $this->buildConnectedUser(null, null, $egapUser);
        }

        $localUser = $this->findLocalUserByCpf($this->normalizeCpf((string) $infoUser->cpf));

        return $this->buildConnectedUser($localUser, $infoUser, $egapUser);
    }

    public function findByLogin(string $login): ?Fluent
    {
        $trimmedLogin = trim($login);

        if ($trimmedLogin === '') {
            return null;
        }

        $localUser = User::query()
            ->where(function (Builder $query) use ($trimmedLogin): void {
                $query
                    ->where('login', $trimmedLogin)
                    ->orWhere('email', $trimmedLogin);
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

    private function buildConnectedUser(?User $user, ?InfoUser $infoUser, ?UserEgap $userEgap): ?Fluent
    {
        if (! $user && ! $infoUser && ! $userEgap) {
            return null;
        }

        $userEgap = $userEgap ? $this->loadEgapUserRelations($userEgap) : null;
        $infoUser ??= $userEgap?->infoUser;
        $lotacao = $userEgap?->lotacoes->first();
        $cpf = $this->firstFilledValue(
            $user?->cpf,
            $infoUser?->cpf,
        );

        return new Fluent([
            'id' => $userEgap?->id ?? $user?->id,
            'user_id' => $user?->id,
            'egap_user_id' => $userEgap?->id,
            'cpf' => $cpf,
            'cpf_normalized' => $cpf ? $this->normalizeCpf($cpf) : null,
            'name' => $this->firstFilledValue($userEgap?->name, $user?->name),
            'login' => $this->firstFilledValue($userEgap?->username, $user?->login),
            'username' => $userEgap?->username,
            'email' => $this->firstFilledValue($userEgap?->email, $user?->email),
            'matricula' => $infoUser?->matricula ?? $user?->matricula,
            'cargo' => $infoUser?->cargo,
            'setor' => $lotacao?->setor,
            'setor_nome' => $lotacao?->setorRef?->Setor,
            'unidade_judiciaria' => $lotacao?->unidade_judiciaria,
            'unidade_judiciaria_nome' => $lotacao?->unidadeJudiciaria?->Setor,
            'lotacao_atualizada_em' => $lotacao?->date_time,
            'user' => $user,
            'info_user' => $infoUser,
            'user_egap' => $userEgap,
            'lotacao' => $lotacao,
        ]);
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
            ->where(function (Builder $query) use ($normalizedCpf): void {
                $query
                    ->where('cpf', $normalizedCpf)
                    ->orWhereRaw($this->normalizeSqlColumn('cpf').' = ?', [$normalizedCpf]);
            })
            ->orderByDesc('date_time')
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
            'lotacoes.unidadeJudiciaria',
            'lotacoes.setorRef',
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

    private function firstFilledValue(mixed ...$values): mixed
    {
        foreach ($values as $value) {
            if ($value !== null && $value !== '') {
                return $value;
            }
        }

        return null;
    }
}
