<?php

namespace App\Models\Egap\Almoxarifado;

use App\Models\Egap\Cadastro\ComplementoSetor;
use App\Models\Egap\Cadastro\Setores;
use App\Models\Egap\Patrimonio\BensMoveis\Termo;
use App\Models\UserEgap;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Pedidos extends Model
{
    protected $connection = 'egap';
    protected $table = 'ped_pedidos';
    public $timestamps = false;

    protected $fillable = [
        'date_time',
        'Solicitante',
        'UnidadeJudiciaria',
        'Setor',
        'Observacao',
        'DataTermino',
        'ResponsavelAtendimento',
        'idSituacao',
        'num_protocolo',
        'arquivo',
        'justificativa',
        'setor_responsavel',
        'ComplementoSetor',
    ];

    protected $casts = [
        'date_time' => 'datetime',
        'DataTermino' => 'datetime',
    ];

    public function termo() : HasOne
    {
        return $this->hasOne(Termo::class, 'pedido_no', 'id');
    }

    public function itens(): HasMany
    {
        return $this->hasMany(ItemPedido::class, 'idPedido', 'id');
    }

    public function itensPedido(): HasMany
    {
        return $this->itens();
    }

    public function fases(): HasMany
    {
        return $this->hasMany(FasePedido::class, 'id_pedido', 'id');
    }

    public function situacao(): BelongsTo
    {
        return $this->belongsTo(SituacaoPedido::class, 'idSituacao', 'id');
    }

    public function solicitante_get(): BelongsTo
    {
        return $this->belongsTo(UserEgap::class, 'Solicitante', 'id');
    }

    public function responsavel_atendimento(): BelongsTo
    {
        return $this->belongsTo(UserEgap::class, 'ResponsavelAtendimento', 'id');
    }

    public function unidade_judiciaria(): BelongsTo
    {
        return $this->belongsTo(Setores::class, 'UnidadeJudiciaria', 'id');
    }

    public function setor_get(): BelongsTo
    {
        return $this->belongsTo(Setores::class, 'Setor', 'id');
    }

    public function setorResponsavel(): BelongsTo
    {
        return $this->belongsTo(Setores::class, 'setor_responsavel', 'id');
    }

    public function complementoSetor(): BelongsTo
    {
        return $this->belongsTo(ComplementoSetor::class, 'ComplementoSetor', 'id');
    }

//    protected static function booted(): void
//    {
//        static::creating(function (self $model) {
//            if (blank($model->date_time)) {
//                $model->date_time = now();
//            }
//        });
//
//        static::updating(function (self $model) {
//            $model->date_time = now();
//        });
//    }
}
