<?php

namespace App\Models\Patrimonio\BensImoveis;

use Illuminate\Database\Eloquent\Model;

class TermoImovel extends Model
{
    protected $connection = 'egap';
    protected $table = 'imo_termosimoveis';
    protected $primaryKey = 'id';
    protected $guarded = [];
    public $timestamps = false;

    public function termoRelacaoref()
    {
        return $this->belongsTo(TermoResponsabilidade::class, 'termo', 'id');
    }

    public function imovelRelacaoref()
    {
        return $this->belongsTo(\App\Models\Patrimonio\BensImoveis\BemImovel::class, 'imovel', 'Id');
    }
}
