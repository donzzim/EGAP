<?php

namespace App\Models\Almoxarifado;

use App\Models\Cadastro\DescricaoDetalhada;
use App\Models\Cadastro\Setores;
use App\Models\UserEgap;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MovimentacaoEstoque extends Model
{
    protected $connection = 'egap';
    protected $table = 'alm_estoque';
    public $timestamps = false;

    protected $fillable = [
        'date_time',
        'tipo_movimentacao',
        'nota_fiscal',
        'material',
        'quantidade',
        'preco_unitario',
        'atualizado_por',
        'valor_total',
        'quantidade_estoque',
        'preco_medio_estoque',
        'valor_total_estoque',
        'id_setor',
        'id_pedido',
    ];

    protected $casts = [
        'date_time' => 'datetime',
        'quantidade' => 'integer',
        'preco_unitario' => 'decimal:10',
        'valor_total' => 'decimal:10',
        'quantidade_estoque' => 'integer',
        'preco_medio_estoque' => 'decimal:10',
        'valor_total_estoque' => 'decimal:10',
    ];

    public function atualizadoPor(): BelongsTo
    {
        return $this->belongsTo(UserEgap::class, 'atualizado_por', 'id');
    }

    public function setor(): BelongsTo
    {
        return $this->belongsTo(Setores::class, 'id_setor', 'id');
    }

    public function notaFiscal(): BelongsTo
    {
        return $this->belongsTo(NotaFiscal::class, 'nota_fiscal', 'id');
    }

    public function materialRel(): BelongsTo
    {
        return $this->belongsTo(DescricaoDetalhada::class, 'material', 'id');
    }

    // Criar model de pedido
//    public function pedido(): BelongsTo
//    {
//        return $this->belongsTo(Pedido::class, 'id_pedido', 'id');
//    }

    public function tipoMovimentacaoRel(): BelongsTo
    {
        return $this->belongsTo(TipoMovimentacaoNotaFiscal::class, 'tipo_movimentacao', 'id');
    }

    protected static function booted(): void
    {
        static::saving(function (self $model) {
            $quantidade = (float) ($model->quantidade ?? 0);
            $precoUnitario = (float) ($model->preco_unitario ?? 0);

            if (empty($model->valor_total) && $quantidade > 0 && $precoUnitario > 0) {
                $model->valor_total = $quantidade * $precoUnitario;
            }
        });
    }
}
