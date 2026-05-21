<?php

namespace App\Models\Cadastro;

use App\Models\UserEgap;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContaContabil extends Model
{
    //
    //protected $connection = 'egap';
    protected $table = 'mat_planocontas';
    public $timestamps = false;
    protected $fillable = [
        'date_time',
        'codigo',
        'titulo',
        'funcao',
        'vinculo',
        'usuario'
    ];

    public function atualizado_por() : BelongsTo
    {
        return $this->belongsTo(UserEgap::class, 'usuario', 'id');
    }

    protected static function booted()
    {
        static::creating(function ($model) {
            $model->usuario = auth()->id();
            $model->date_time = now();
        });
    }

}
