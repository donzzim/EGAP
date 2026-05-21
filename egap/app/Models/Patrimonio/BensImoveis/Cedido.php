<?php

namespace App\Models\Patrimonio\BensImoveis;

use Illuminate\Database\Eloquent\Model;

class Cedido extends Model
{
    //protected $connection = 'egap';
    protected $table = 'imo_cedidos';
    protected $primaryKey = 'id';
    protected $guarded = [];
    public $timestamps = false;

    protected $casts = [
        'despesas' => 'array',
    ];

    public function gestores()
    {
        return $this->hasMany(GestorCedido::class, 'id_cedidos', 'id');
    }

    public function imovelRelacaoref()
    {
        return $this->belongsTo(\App\Models\Patrimonio\BensImoveis\BemImovel::class, 'id_imovel', 'Id');
    }

    public function atualizadoPorRelacaoref()
    {
        return $this->belongsTo(\App\Models\UserEgap::class, 'atualizado_por', 'id');
    }

    public function tipoTributoRelacaoref()
    {
        return $this->belongsTo(\App\Models\Patrimonio\BensImoveis\TipoTributo::class, 'tipo_tributo', 'id');
    }
}
