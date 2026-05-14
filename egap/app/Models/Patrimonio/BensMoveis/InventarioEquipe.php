<?php

namespace App\Models\Egap\Patrimonio\BensMoveis;

use App\Models\UserEgap;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventarioEquipe extends Model
{
    protected $connection = 'egap';
    protected $table = 'inv_equipes';
    public $timestamps = false;

    protected $fillable = [
        'date_time', 'id_inventario', 'unidade', 'funcao', 'integrante'
    ];

    /** ✅ RELAÇÃO COM A ATIVIDADE/UNIDADE */
    public function unidadeAtividade(): BelongsTo 
    { 
        return $this->belongsTo(InventarioUnidade::class, 'unidade', 'id'); 
    }

    /** ✅ RELAÇÃO COM O USUÁRIO (integrante) */
    public function membroRef(): BelongsTo 
    { 
        return $this->belongsTo(UserEgap::class, 'integrante', 'id'); 
    }
}