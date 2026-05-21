<?php

namespace App\Models\Agendamento;

use App\Models\Cadastro\Marcas;
use App\Models\Cadastro\Modelos;
use App\Models\UserEgap;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Frota extends Model
{
    //protected $connection = 'egap';
    protected $table = 'age_frota';

    protected $primaryKey = 'id';

    public $timestamps = false;

    protected $fillable = [
        'date_time',
        'id_user',
        'descricao',
        'marca',
        'modelo',
        'placa',
        'proprietario',
        'disponivel',
        'ativo',
    ];

    protected $casts = [
        'date_time' => 'datetime',
        'disponivel' => 'boolean',
        'ativo' => 'boolean',
    ];

    public function marcaRef() : BelongsTo
    {
        return $this->belongsTo(Marcas::class, 'marca', 'id');
    }
    public function modeloRef() : BelongsTo
    {
        return $this->belongsTo(Modelos::class, 'modelo', 'id');
    }

    public function idUserRef() : BelongsTo
    {
        return $this->belongsTo(UserEgap::class, 'id_user', 'id');
    }

    protected static function booted()
    {
        static::creating(function ($model) {
            $model->id_user = auth()->id();
            $model->date_time = now();
        });

        static::updating(function ($model) {
            $model->id_user = auth()->id();
            $model->date_time = now();
        });
    }
}
