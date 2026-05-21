<?php

namespace App\Models\Cadastro;

use Illuminate\Database\Eloquent\Model;

class CentroCusto extends Model
{
    //
    //protected $connection = 'egap';
    public $timestamps = false;
    protected $table = 'cad_centrocusto';
    protected $fillable = [
        'date_time',
        'codigo',
        'descricao',
    ];

    protected static function booted()
    {
        static::creating(function ($model) {
            $model->date_time = now();
        });

        static::updating(function ($model) {
            $model->date_time = now();
        });
    }
}
