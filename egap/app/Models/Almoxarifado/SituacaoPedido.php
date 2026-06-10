<?php

namespace App\Models\Almoxarifado;

use App\Models\UserEgap;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SituacaoPedido extends Model
{
    //
    //protected $connection = 'egap';
    public $timestamps = false;
    protected $table = 'ped_situacao';
    protected $fillable = [
        'date_time',
        'Descricao',
        'Usuario',
        'modulo'
    ];

    public function atualizadoPor() : BelongsTo
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
