<?php

namespace App\Models\Cadastro;

use App\Models\UserEgap;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ElementoDespesa extends Model
{
    //protected $connection = 'egap';
    protected $table = 'mat_produtos';

    protected $fillable = [
        'CodigodaClasse',
        'DescricaodaClasse',
        'Despesa',
        'VidaUtil',
        'date_time',
        'Usuario',
        'ValorResidual',
        'item_patrimonial',
    ];

    public function atualizado_por(): BelongsTo
    {
        return $this->belongsTo(UserEgap::class, 'Usuario', 'id');
    }

    protected static function booted()
    {
        static::creating(function ($model) {
            $model->date_time = now();
            $model->Usuario = auth()->id();
        });
    }
}
