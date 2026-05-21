<?php

namespace App\Models\Cadastro;

use App\Models\UserEgap;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Modelos extends Model
{
    //protected $connection = 'egap';
    protected $table = 'mat_modelo';
    protected $fillable = [
        'date_time',
        'Usuario',
        'marca',
        'descricao',
    ];

    public function marca_ref(): BelongsTo
    {
        return $this->belongsTo(Marcas::class, 'marca', 'id');
    }

    public function atualizado_por() : BelongsTo
    {
        return $this->belongsTo(UserEgap::class, 'Usuario', 'id');
    }

    protected static function booted()
    {
        static::creating(function ($model) {
            $model->Usuario = auth()->id();
            $model->date_time = now();
        });
    }
}
