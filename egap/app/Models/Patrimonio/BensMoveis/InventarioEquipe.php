<?php

namespace App\Models\Patrimonio\BensMoveis;

use App\Models\UserEgap;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventarioEquipe extends Model
{
    //protected $connection = 'egap';
    protected $table = 'inv_equipes';
    public $timestamps = false;

    protected $fillable = [
        'date_time', 'id_inventario', 'unidade', 'funcao', 'integrante'
    ];

    protected $casts = [
        'date_time' => 'datetime',
    ];

    public function inventario(): BelongsTo
    {
        return $this->belongsTo(Inventario::class, 'id_inventario', 'id');
    }

    public function unidadeInventariada(): BelongsTo
    {
        return $this->belongsTo(InventarioUnidade::class, 'unidade', 'id');
    }

    public function integrantesRef(): BelongsTo
    {
        return $this->belongsTo(UserEgap::class, 'integrante', 'id');
    }

    public function membroRef(): BelongsTo
    {
        return $this->integrantesRef();
    }

    protected static function booted(): void
    {
        static::saving(function (self $model): void {
            $model->date_time = now();

            if (! $model->id_inventario && $model->unidade) {
                $model->id_inventario = InventarioUnidade::query()
                    ->whereKey($model->unidade)
                    ->value('id_inventario');
            }
        });
    }
}
