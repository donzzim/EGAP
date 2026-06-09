<?php

namespace App\Models\Patrimonio\BensImoveis;

use Illuminate\Database\Eloquent\Model;
use App\Models\Patrimonio\BensImoveis\BemImovel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Depreciacao extends Model
{
    //protected $connection = 'egap';
    protected $table = 'imo_depreciacao';
    protected $primaryKey = 'Id';
    protected $guarded = ['Id'];

    public function imovelRelacaoref() : BelongsTo
    {
        return $this->belongsTo(BemImovel::class, 'Id_imovel', 'Id');
    }

    public function obraRelacaoref() : BelongsTo
    {
        return $this->belongsTo(Obra::class, 'id_obra', 'id');
    }
}
