<?php

namespace App\Models\Patrimonio\BensMoveis;

use App\Models\UserEgap;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;

class Inventario extends Model
{
    //protected $connection = 'egap';

    /** ✅ O FIX: Garante que o Laravel use mat_inventario e não mat_table */
    protected $table = 'mat_inventario';

    public $timestamps = false;
    public static $snakeAttributes = false;

    protected $fillable = [
        'date_time', 'num_inventario', 'ano_inventario', 'inicio_inventario',
        'termino_inventario', 'atualizado_em', 'atualizado_por', 'situacao', 'dias'
    ];

    public function itens(): HasMany
    {
        return $this->hasMany(ItemInventario::class, 'id_inventario', 'id');
    }

    public function comissoes(): HasMany
    {
        return $this->hasMany(InventarioComissao::class, 'id_inventario', 'id');
    }

    public function responsavel(): BelongsTo
    {
        return $this->belongsTo(UserEgap::class, 'atualizado_por', 'id');
    }

    protected static function booted()
    {
        static::creating(function ($model) {
            $model->atualizado_por = Auth::id();
            $model->date_time = now();
            $model->atualizado_em = now();
        });
    }
}
