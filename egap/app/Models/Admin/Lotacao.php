<?php

namespace App\Models\Admin;

use App\Models\Cadastro\Setores;
use App\Models\UserEgap;
use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Lotacao extends Model
{
    //protected $connection = 'egap';
    protected $table = 'mat_lotacao';

    public $timestamps = false;

    protected $fillable = [
        'date_time',
        'usuario',
        'id_user',
        'unidade_judiciaria',
        'setor',
    ];

    protected $casts = [
        'date_time' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(UserEgap::class, 'id_user', 'id');
    }

    public function usuarioRef(): BelongsTo
    {
        return $this->belongsTo(UserEgap::class, 'usuario', 'id');
    }

    public function unidadeJudiciaria(): BelongsTo
    {
        return $this->belongsTo(Setores::class, 'unidade_judiciaria', 'id');
    }

    public function setorRef(): BelongsTo
    {
        return $this->belongsTo(Setores::class, 'setor', 'id');
    }

    protected static function booted(): void
    {
        static::creating(function (self $model): void {
            $model->date_time = $model->date_time ?? now();
            $model->usuario = static::resolveAuthenticatedEgapUserId();
        });

        static::updating(function (self $model): void {
            $model->date_time = now();
            $model->usuario = static::resolveAuthenticatedEgapUserId();
        });
    }

    protected static function resolveAuthenticatedEgapUserId(): ?int
    {
        $user = Filament::auth()->user()
            ?? auth('pessoa')->user()
            ?? auth()->user();

        if ($user instanceof UserEgap) {
            return $user->getKey();
        }

        $login = $user?->login ?? $user?->username ?? null;

        if (blank($login)) {
            return null;
        }

        return UserEgap::query()
            ->where('username', $login)
            ->value('id');
    }
}
