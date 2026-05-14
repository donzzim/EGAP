<?php

namespace App\Models\Agendamento;

use App\Models\Almoxarifado\Pedidos;
use App\Models\Patrimonio\BensMoveis\Termo;
use App\Models\UserEgap;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Materiais extends Model
{
    public const TIPO_TRANSPORTE_CARGA = '2';

    protected $connection = 'egap';
    protected $table = 'age_materiais';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'date_time',
        'id_user',
        'id_pedido',
        'id_termo',
        'id_solicitacao',
        'data_recebimento',
        'data_entrega',
    ];

    public function scopeTipoTransporteCarga(Builder $query): Builder
    {
        return $query->whereHas('idSolicitacaoRef', function (Builder $solicitacaoQuery): void {
            $solicitacaoQuery->where('tipo', self::TIPO_TRANSPORTE_CARGA);
        });
    }

    public function idSolicitacaoRef(): BelongsTo
    {
        return $this->belongsTo(Solicitacao::class, 'id_solicitacao', 'id');
    }

    public function idPedidoRef(): BelongsTo
    {
        return $this->belongsTo(Pedidos::class, 'id_pedido', 'id');
    }

    public function idTermoRef(): BelongsTo
    {
        return $this->belongsTo(Termo::class, 'id_termo', 'id');
    }

    public function idUserRef(): BelongsTo
    {
        return $this->belongsTo(UserEgap::class, 'id_user', 'id');
    }

    protected static function booted()
    {
        static::creating(function ($model) {
            $model->id_user = auth()->id();
            $model->date_time = now();
        });

        static::updating(function ($model) {
            $model->id_user = auth()->id();
            $model->date_time = now();
        });
    }
}
