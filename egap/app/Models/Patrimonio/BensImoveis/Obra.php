<?php

namespace App\Models\Egap\Patrimonio\BensImoveis;

use Illuminate\Database\Eloquent\Model;

class Obra extends Model
{
    protected $connection = 'egap';
    protected $table = 'imo_obras';
    protected $primaryKey = 'id';
    protected $guarded = [];
    public $timestamps = false;

    public function imovelRelacaoref()
    {
        return $this->belongsTo(\App\Models\Egap\Patrimonio\BensImoveis\BemImovel::class, 'id_imovel', 'Id');
    }

    public function atualizadoPorRelacaoref()
    {
        return $this->belongsTo(\App\Models\UserEgap::class, 'atualizado_por', 'id');
    }
}