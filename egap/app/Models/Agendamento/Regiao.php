<?php

namespace App\Models\Agendamento;

use App\Models\Cadastro\Setores;
use App\Models\UserEgap;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Regiao extends Model
{
    //protected $connection = 'egap';
    protected $table = 'age_regiao';

    protected $primaryKey = 'id';

    public $timestamps = false;

    protected $fillable = [
        'date_time',
        'regiao',
        'unidade',
        'atualizado_por',
        'sigla',
    ];

    public function unidadeRef() : BelongsTo
    {
        return $this->belongsTo(Setores::class, 'unidade', 'id');
    }

    public function atualizadoPorRef() : BelongsTo
    {
        return $this->belongsTo(UserEgap::class, 'atualizado_por', 'id');
    }

    public function solicitacoes() : HasMany
    {
        return $this->hasMany(Solicitacao::class, 'regiao', 'id');
    }

    protected static function booted()
    {
        static::creating(function ($model) {
            $model->atualizado_por = auth()->id();
            $model->date_time = now();
        });

        static::updating(function ($model) {
            $model->atualizado_por = auth()->id();
            $model->date_time = now();
        });
    }
}
