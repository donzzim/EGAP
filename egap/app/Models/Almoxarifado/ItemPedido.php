<?php

namespace App\Models\Egap\Almoxarifado;

use App\Models\Egap\Cadastro\DescricaoDetalhada;
use App\Models\Egap\Cadastro\DescricaoResumida;
use App\Models\UserEgap;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ItemPedido extends Model
{
    protected $connection = 'egap';
    protected $table = 'ped_itempedido';
    public $timestamps = false;

    protected $fillable = [
        'date_time',
        'idPedido',
        'QuantidadeMaterial',
        'ObservacaoItem',
        'QuantidadeMaterialAtendida',
        'material',
        'DescricaoDetalhada',
        'data_validacao',
        'situacao',
        'justificativa',
        'validado_por',
        'data_cancelado',
        'cancelado_por',
        'quantidade_validada',
        'valor_material',
    ];

    protected $casts = [
        'date_time' => 'datetime',
        'data_validacao' => 'datetime',
        'data_cancelado' => 'datetime',
        'valor_material' => 'decimal:10',
        'QuantidadeMaterial' => 'integer',
        'quantidade_validada' => 'integer',
        'QuantidadeMaterialAtendida' => 'integer',
    ];

    public function pedido(): BelongsTo
    {
        return $this->belongsTo(Pedidos::class, 'idPedido', 'id');
    }

    public function materialRel(): BelongsTo
    {
        return $this->belongsTo(DescricaoResumida::class, 'material', 'id');
    }

    public function descricaoDetalhadaRel(): BelongsTo
    {
        return $this->belongsTo(DescricaoDetalhada::class, 'DescricaoDetalhada', 'id');
    }

    public function situacaoRef(): BelongsTo
    {
        return $this->belongsTo(SituacaoPedido::class, 'situacao', 'id');
    }

    public function validadoPor(): BelongsTo
    {
        return $this->belongsTo(UserEgap::class, 'validado_por', 'id');
    }

    public function canceladoPor(): BelongsTo
    {
        return $this->belongsTo(UserEgap::class, 'cancelado_por', 'id');
    }

    public function getMaterialNomeAttribute(): string
    {
        return $this->descricaoDetalhadaRel?->descricao_detalhada
            ?? $this->materialRel?->Descricao
            ?? 'Material sem descrição';
    }

    public function getQuantidadeSolicitadaAttribute(): int
    {
        return (int) ($this->QuantidadeMaterial ?? 0);
    }

    public function getQuantidadeValidadaCalcAttribute(): int
    {
        return (int) ($this->quantidade_validada ?? 0);
    }

    public function getQuantidadeAtendidaAttribute(): int
    {
        return (int) ($this->QuantidadeMaterialAtendida ?? 0);
    }

    public function getQuantidadePendenteAttribute(): int
    {
        $solicitada = (int) ($this->QuantidadeMaterial ?? 0);
        $validada = (int) ($this->quantidade_validada ?? 0);
        $atendida = (int) ($this->QuantidadeMaterialAtendida ?? 0);

        $base = $validada > 0 ? $validada : $solicitada;

        return max($base - $atendida, 0);
    }

    public function scopePendentes(Builder $query): Builder
    {
        return $query->where(function (Builder $query) {
            $query
                ->whereRaw('COALESCE(quantidade_validada, 0) > COALESCE(QuantidadeMaterialAtendida, 0)')
                ->orWhere(function (Builder $subQuery) {
                    $subQuery
                        ->where(function (Builder $q) {
                            $q->whereNull('quantidade_validada')
                                ->orWhere('quantidade_validada', 0);
                        })
                        ->whereRaw('COALESCE(QuantidadeMaterial, 0) > COALESCE(QuantidadeMaterialAtendida, 0)');
                });
        });
    }

    protected static function booted(): void
    {
        static::creating(function (self $model) {
            if (blank($model->date_time)) {
                $model->date_time = now();
            }
        });

        static::updating(function (self $model) {
            if (blank($model->date_time)) {
                $model->date_time = now();
            }
        });
    }
}
