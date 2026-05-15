<?php

namespace App\Models;

use App\Models\Admin\InfoUser;
use App\Models\Admin\Lotacao;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Model;
use JsonSerializable;

class UserMobile extends Model implements Arrayable, JsonSerializable
{
    protected $table = 'users';

    protected $hidden = [
        'password',
        'remember_token',
    ];

    private ?User $baseUser = null;

    private ?InfoUser $infoUserMobile = null;

    private ?Lotacao $lotacaoMobile = null;

    private ?string $plainTextToken = null;

    public static function fromLinkedUsers(User $user, InfoUser $infoUser, ?Lotacao $lotacao): self
    {
        $mobileUser = new self;
        $mobileUser->baseUser = $user;
        $mobileUser->setConnection($user->getConnectionName());
        $mobileUser->setRawAttributes($user->getAttributes(), true);
        $mobileUser->exists = $user->exists;
        $mobileUser->wasRecentlyCreated = $user->wasRecentlyCreated;
        $mobileUser->setMobileContext($infoUser, $lotacao);

        return $mobileUser;
    }

    public function baseUser(): ?User
    {
        return $this->baseUser;
    }

    public function setMobileContext(InfoUser $infoUser, ?Lotacao $lotacao): void
    {
        $this->infoUserMobile = $infoUser;
        $this->lotacaoMobile = $lotacao;
    }

    public function setMobileToken(string $token): void
    {
        $this->plainTextToken = $token;
    }

    public function id(): int
    {
        return (int) $this->getKey();
    }

    public function idEgap(): ?int
    {
        $usuarioId = $this->infoUserMobile()?->usuario_id;

        return $usuarioId === null ? null : (int) $usuarioId;
    }

    public function login(): ?string
    {
        return $this->getAttribute('login');
    }

    public function name(): ?string
    {
        return $this->getAttribute('name');
    }

    public function email(): ?string
    {
        return $this->getAttribute('email');
    }

    public function unidadeJudiciaria(): int|string|null
    {
        return $this->lotacaoMobile()?->unidade_judiciaria;
    }

    public function setor(): int|string|null
    {
        return $this->lotacaoMobile()?->setor;
    }

    public function token(): ?string
    {
        return $this->plainTextToken;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id(),
            'idEgap' => $this->idEgap(),
            'login' => $this->login(),
            'name' => $this->name(),
            'email' => $this->email(),
            'unidade_judiciaria' => $this->unidadeJudiciaria(),
            'setor' => $this->setor(),
            'token' => $this->token(),
        ];
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    private function infoUserMobile(): ?InfoUser
    {
        if ($this->infoUserMobile !== null) {
            return $this->infoUserMobile;
        }

        $cpf = $this->getAttribute('cpf');

        if (blank($cpf)) {
            return null;
        }

        $normalizedCpf = preg_replace('/\D+/', '', (string) $cpf) ?? '';

        if ($normalizedCpf === '') {
            return null;
        }

        $this->infoUserMobile = InfoUser::query()
            ->whereNotNull('cpf')
            ->whereNotNull('usuario_id')
            ->whereRaw($this->normalizeSqlColumn('cpf').' = ?', [$normalizedCpf])
            ->orderByDesc('date_time')
            ->first();

        return $this->infoUserMobile;
    }

    private function lotacaoMobile(): ?Lotacao
    {
        if ($this->lotacaoMobile !== null) {
            return $this->lotacaoMobile;
        }

        $idEgap = $this->idEgap();

        if ($idEgap === null) {
            return null;
        }

        $this->lotacaoMobile = Lotacao::query()
            ->where('id_user', $idEgap)
            ->orderByDesc('date_time')
            ->orderByDesc('id')
            ->first();

        return $this->lotacaoMobile;
    }

    private function normalizeSqlColumn(string $column): string
    {
        return "REPLACE(REPLACE(REPLACE(REPLACE(REPLACE({$column}, '.', ''), '-', ''), '/', ''), ' ', ''), ',', '')";
    }
}
