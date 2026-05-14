<?php

namespace App\Models\Egap\Cadastro;

use App\Models\UserEgap;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Setores extends Model
{
    protected $connection = 'egap';
    protected $table = 'mat_setores';

    protected $fillable = [
        'date_time',
        'CodigoPai',
        'UnidadeOrganizacional',
        'Setor',
        'Usuario',
        'CodigodaUO',
        'comarca',
        'vara',
        'ordem',
        'email',
        'cd_orgao',
        'cns',
        'presidencia',
        'centrocusto',
    ];

    public $timestamps = false;

    /*
    |--------------------------------------------------------------------------
    | Relacionamentos
    |--------------------------------------------------------------------------
    */

    // Usuário que criou/alterou
    public function atualizado_por(): BelongsTo
    {
        return $this->belongsTo(UserEgap::class, 'Usuario', 'id');
    }

    // Hierarquia (pai)
    public function pai(): BelongsTo
    {
        return $this->belongsTo(Setores::class, 'CodigoPai');
    }

    // Hierarquia (filhos)
    public function filhos(): HasMany
    {
        return $this->hasMany(Setores::class, 'CodigoPai');
    }

    /*
    |--------------------------------------------------------------------------
    | Auditoria automática
    |--------------------------------------------------------------------------
    */

    protected static function booted()
    {
        static::creating(function ($model) {
            $model->Usuario = auth()->id();
            $model->date_time = now();
        });
    }
}
