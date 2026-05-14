<?php

namespace App\Models\Patrimonio\BensImoveis;

use Illuminate\Database\Eloquent\Model;

class TipoTributo extends Model
{
    protected $connection = 'egap';
    protected $table = 'imo_tipotributo';
    protected $guarded = [];
    public $timestamps = false;

    public function atualizadoPorRelacaoref()
    {
        return $this->belongsTo(\App\Models\UserEgap::class, 'atualizado_por', 'id');
    }

    protected static function booted()
    {
        static::creating(function ($model) {
            $model->atualizado_por = auth()->id() ?? 1;
            $model->date_time = now();
        });

        static::updating(function ($model) {
            $model->atualizado_por = auth()->id() ?? 1;
            $model->date_time = now();
        });
    }
}
