<?php

namespace App\Models\Patrimonio\BensImoveis;

use Illuminate\Database\Eloquent\Model;

class TermoResponsabilidade extends Model
{
    //protected $connection = 'egap';
    protected $table = 'imo_termos';
    protected $primaryKey = 'id';
    protected $guarded = [];
    public $timestamps = false;

    public function atualizadoPorRelacaoref()
    {
        return $this->belongsTo(\App\Models\UserEgap::class, 'atualizado_por', 'id');
    }

    public function termosImoveis()
    {
        return $this->hasMany(TermoImovel::class, 'termo', 'id');
    }
}
