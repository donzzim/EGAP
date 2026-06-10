<?php

namespace App\Models\Patrimonio\BensImoveis;

use Illuminate\Database\Eloquent\Model;

class GestorCedido extends Model
{
    //protected $connection = 'egap';
    protected $table = 'imo_gestorcedidos';
    protected $primaryKey = 'id';
    protected $guarded = [];
    public $timestamps = false;

    public function nomeRelacaoref()
    {
        return $this->belongsTo(\App\Models\UserEgap::class, 'nome', 'id');
    }

    public function atualizadoPorRelacaoref()
    {
        return $this->belongsTo(\App\Models\UserEgap::class, 'atualizado_por', 'id');
    }

    protected static function booted(): void
    {
        static::saving(function (self $model): void {
            $model->date_time = now();
            $model->atualizado_por = auth()->id();
        });
    }
}
