<?php

namespace App\Models;

use App\Models\Admin\InfoUser;
use App\Models\Admin\Lotacao;
use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class UserEgap extends Model
{
    // protected $connection = 'egap';
    protected $table = 'jos_users';

    protected $primaryKey = 'id';

    public $timestamps = false;

    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'block',
        'sendEmail',
        'registerDate',
        'lastvisitDate',
        'activation',
        'params',
        'lastResetTime',
        'resetCount',
        'otpKey',
        'otep',
        'requireReset',
    ];

    protected $casts = [
        'block' => 'boolean',
        'sendEmail' => 'boolean',
        'requireReset' => 'boolean',
        'registerDate' => 'datetime',
        'lastvisitDate' => 'datetime',
        'lastResetTime' => 'datetime',
    ];

    public function infoUser(): HasOne
    {
        return $this->hasOne(InfoUser::class, 'usuario_id', 'id');
    }

    public function lotacoes(): HasMany
    {
        return $this->hasMany(Lotacao::class, 'id_user', 'id')
            ->orderByDesc('date_time')
            ->orderByDesc('id');
    }

    /**
     * Resolve o registro jos_users correspondente ao usuário autenticado no painel
     * (App\Models\User, tabela `users`), casando pelo login/username.
     */
    public static function currentAuthenticated(): ?self
    {
        $user = Filament::getCurrentPanel()
            ? Filament::auth()->user()
            : null;

        $user ??= auth('pessoa')->user()
            ?? auth()->user();

        if ($user instanceof self) {
            return $user;
        }

        $login = $user?->login ?? $user?->username ?? null;

        if (blank($login)) {
            return null;
        }

        return static::query()->where('username', $login)->first();
    }
}
