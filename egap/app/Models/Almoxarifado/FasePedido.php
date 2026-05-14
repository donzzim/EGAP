<?php

namespace App\Models\Egap\Almoxarifado;

use App\Models\Egap\Cadastro\DescricaoDetalhada;
use App\Models\Egap\Cadastro\DescricaoResumida;
use App\Models\Egap\Patrimonio\BensMoveis\Termo;
use App\Models\UserEgap;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

class FasePedido extends Model
{
    //
    protected $connection = 'egap';
    public $timestamps = false;
    protected $table = 'ped_fases';
    protected $fillable = [
        'date_time',
        'idSituacao',
        'Descricao',
        'Usuario',
        'id_pedido',
        'id_itempedido',
        'id_descricaoresumida',
        'id_descricaodetalhada',
        'quantidade',
        'id_termo',
    ];

    protected $casts = [
        'date_time' => 'datetime',
        'quantidade' => 'integer',
    ];

    public function situacaoRef(): BelongsTo
    {
        return $this->belongsTo(SituacaoPedido::class, 'idSituacao', 'id');
    }

    public function usuarioRef(): BelongsTo
    {
        return $this->belongsTo(UserEgap::class, 'Usuario', 'id');
    }

    public function pedidoRef(): BelongsTo
    {
        return $this->belongsTo(Pedidos::class, 'id_pedido', 'id');
    }

    public function itemPedidoRef(): BelongsTo
    {
        return $this->belongsTo(ItemPedido::class, 'id_itempedido', 'id');
    }

    public function descricaoResumidaRef(): BelongsTo
    {
        return $this->belongsTo(DescricaoResumida::class, 'id_descricaoresumida', 'id');
    }

    public function descricaoDetalhadaRef(): BelongsTo
    {
        return $this->belongsTo(DescricaoDetalhada::class, 'id_descricaodetalhada', 'id');
    }

    public function termoRef(): BelongsTo
    {
        return $this->belongsTo(Termo::class, 'id_termo', 'id');
    }

    protected static function booted(): void
    {
        static::creating(function (self $model) {
            $model->date_time ??= now();

            if (Auth::check()) {
                $model->Usuario = Auth::id();
            }
        });

        static::updating(function (self $model) {
            $model->atualizado_em = now()->toDateString();

            if (Auth::check()) {
                $model->Usuario = Auth::id();
            }
        });
    }
}
