<?php

namespace App\Models\Patrimonio\BensImoveis;

use Illuminate\Database\Eloquent\Model;

class Depreciacao extends Model
{
    protected $connection = 'egap';
    protected $table = 'imo_depreciacao';
    protected $primaryKey = 'Id';
    protected $guarded = [];
    public $timestamps = false;

    public function imovelRelacaoref()
    {
        return $this->belongsTo(\App\Models\Patrimonio\BensImoveis\BemImovel::class, 'Id_imovel', 'Id');
    }

    public function obraRelacaoref()
    {
        return $this->belongsTo(Obra::class, 'id_obra', 'id');
    }
}
