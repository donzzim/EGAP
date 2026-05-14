<?php

namespace App\Models\Egap\Patrimonio\BensImoveis;

use Illuminate\Database\Eloquent\Model;

class Tributo extends Model
{
    protected $connection = 'egap';
    protected $table = 'imo_tributos';
    protected $primaryKey = 'Id';
    protected $guarded = [];
    public $timestamps = false;

    protected $casts = [
        'eventos' => 'array',
    ];

    public function imovelRelacaoref()
    {
        return $this->belongsTo(\App\Models\Egap\Patrimonio\BensImoveis\BemImovel::class, 'Id_imovel', 'Id');
    }

    public function tipoTributoRelacaoref()
    {
        return $this->belongsTo(\App\Models\Egap\Patrimonio\BensImoveis\TipoTributo::class, 'tipo_tributo', 'id');
    }

    public function atualizadoPorRelacaoref()
    {
        return $this->belongsTo(\App\Models\UserEgap::class, 'atualizado_por', 'id');
    }
}