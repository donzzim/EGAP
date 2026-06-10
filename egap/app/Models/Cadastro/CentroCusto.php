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

    protected static function booted(): void
    {
        static::saving(function (self $model): void {
            $model->date_time = now();
        });
    }
}
