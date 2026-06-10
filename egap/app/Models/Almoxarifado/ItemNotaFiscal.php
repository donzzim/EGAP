<?php

namespace App\Models\Almoxarifado;

use App\Models\Cadastro\DescricaoDetalhada;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ItemNotaFiscal extends Model
{
    //protected $connection = 'egap';
    protected $table = 'alm_itens_notafiscal';

    protected $fillable = [
        'date_time',
        'id_notafiscal',
        'id_material',
        'quantidade',
        'preco_unitario',
        'tipo_material',
        'id_material_permanente',
        'atualizado_por',
        'total_item',
    ];

    protected $casts = [
        'date_time' => 'datetime',
        'quantidade' => 'integer',
        'preco_unitario' => 'decimal:2',
        'total_item' => 'decimal:2',
    ];

    public $timestamps = false;

    public function notaFiscal(): BelongsTo
    {
        return $this->belongsTo(NotaFiscal::class, 'id_notafiscal', 'id');
    }

    public function material(): BelongsTo
    {
        return $this->belongsTo(DescricaoDetalhada::class, 'id_material', 'id');
    }

    protected static function booted(): void
    {
        static::saving(function (self $model): void {
            $model->date_time = now();
            $model->atualizado_por = auth()->id();
            $model->calcularTotal();
        });

        static::saved(function (self $model): void {
            $model->notaFiscal?->calcularValorTotal();
            $model->notaFiscal?->saveQuietly();
        });

        static::deleted(function (self $model): void {
            $model->notaFiscal?->calcularValorTotal();
            $model->notaFiscal?->saveQuietly();
        });
    }

    public function calcularTotal(): void
    {
        $this->total_item = ($this->quantidade ?? 0) * ($this->preco_unitario ?? 0);
    }
}
