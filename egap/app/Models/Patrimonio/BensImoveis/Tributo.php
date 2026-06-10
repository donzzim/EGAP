<?php

namespace App\Models\Patrimonio\BensImoveis;

use Illuminate\Database\Eloquent\Model;

class Tributo extends Model
{
    //protected $connection = 'egap';
    protected $table = 'imo_tributos';
    protected $primaryKey = 'Id';
    protected $guarded = [];
    public $timestamps = false;

    protected $casts = [
        'eventos' => 'array',
    ];

    public function imovelRelacaoref()
    {
        return $this->belongsTo(\App\Models\Patrimonio\BensImoveis\BemImovel::class, 'Id_imovel', 'Id');
    }

    public function tipoTributoRelacaoref()
    {
        return $this->belongsTo(\App\Models\Patrimonio\BensImoveis\TipoTributo::class, 'tipo_tributo', 'id');
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
