<?php

namespace App\Models\Egap\Patrimonio\BensImoveis;

use Illuminate\Database\Eloquent\Model;

class GestorCedido extends Model
{
    protected $connection = 'egap';
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
}