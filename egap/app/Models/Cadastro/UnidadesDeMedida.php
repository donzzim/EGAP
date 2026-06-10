<?php

namespace App\Models\Cadastro;

use App\Models\UserEgap;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UnidadesDeMedida extends Model
{
    //protected $connection = 'egap';
    protected $table = 'mat_unidades';
    public $timestamps = false;
    protected $fillable = [
        'date-time',
        'Sigla',
        'Unidade',
        'Usuario',
    ];

    public function atualizado_por() : BelongsTo
    {
        return $this->belongsTo(UserEgap::class, 'Usuario', 'id');
    }

    protected static function booted(): void
    {
        static::saving(function (self $model): void {
            $model->date_time = now();
            $model->Usuario = auth()->id();
        });
    }
}
