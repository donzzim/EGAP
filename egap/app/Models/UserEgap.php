<?php

namespace App\Models;

use App\Models\Admin\InfoUser;
use App\Models\Admin\Lotacao;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class UserEgap extends Model
{
    protected $connection = 'egap';
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
}
