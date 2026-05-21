<?php

namespace App\Models\Patrimonio\BensMoveis;

use App\Models\UserEgap;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventarioComissao extends Model
{
    //protected $connection = 'egap';
    protected $table = 'inv_comissao';
    public $timestamps = false;

    protected $fillable = [
        'date_time', 'id_inventario', 'comissao', 'nome', 'funcao'
    ];

    public function inventario(): BelongsTo
    {
        return $this->belongsTo(Inventario::class, 'id_inventario', 'id');
    }

    /** ✅ RELAÇÃO COM O USUÁRIO: A coluna 'nome' guarda o ID da jos_users */
    public function membroRef(): BelongsTo
    {
        return $this->belongsTo(UserEgap::class, 'nome', 'id');
    }
}
