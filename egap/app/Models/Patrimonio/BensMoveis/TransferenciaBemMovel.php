<?php

namespace App\Models\Patrimonio\BensMoveis;

use App\Models\Almoxarifado\Pedidos;
use App\Models\Cadastro\ComplementoSetor;
use App\Models\Cadastro\Setores;
use App\Models\UserEgap;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TransferenciaBemMovel extends Model
{
    //protected $connection = 'egap';
    protected $table = 'mat_transferencia';
    public $timestamps = false;

    protected $fillable = [
        'date_time',
        'NumPatrimonio',
        'SetorAnterior',
        'SetorAtual',
        'Usuario',
        'UnidadeAnterior',
        'UnidadeAtual',
        'ComplementoAnterior',
        'ComplementoAtual',
        'AndarAnterior',
        'AndarAtual',
        'Termo',
        'pedido_no',
    ];

    public function bem(): BelongsTo
    {
        return $this->belongsTo(BemMovel::class, 'NumPatrimonio', 'id');
    }

    public function unidadeAnteriorRel(): BelongsTo
    {
        return $this->belongsTo(Setores::class, 'UnidadeAnterior', 'id');
    }

    public function unidadeAtualRel(): BelongsTo
    {
        return $this->belongsTo(Setores::class, 'UnidadeAtual', 'id');
    }

    public function setorAnteriorRel(): BelongsTo
    {
        return $this->belongsTo(Setores::class, 'SetorAnterior', 'id');
    }

    public function setorAtualRel(): BelongsTo
    {
        return $this->belongsTo(Setores::class, 'SetorAtual', 'id');
    }

    public function complementoAnteriorRel(): BelongsTo
    {
        return $this->belongsTo(ComplementoSetor::class, 'ComplementoAnterior', 'id');
    }

    public function complementoAtualRel(): BelongsTo
    {
        return $this->belongsTo(ComplementoSetor::class, 'ComplementoAtual', 'id');
    }

    public function usuarioRef(): BelongsTo
    {
        return $this->belongsTo(UserEgap::class, 'Usuario', 'id');
    }

    public function termoRel(): BelongsTo
    {
        return $this->belongsTo(Termo::class, 'Termo', 'id');
    }

    public function pedido(): BelongsTo
    {
        return $this->belongsTo(Pedidos::class, 'pedido_no', 'id');
    }

    protected static function booted(): void
    {
        static::creating(function (self $model) {
            if (blank($model->date_time)) {
                $model->date_time = now();
            }

            if (blank($model->Usuario) && filament()->auth()->check()) {
                $model->Usuario = filament()->auth()->id();
            }
        });

        static::updating(function (self $model) {
            $model->date_time = now();

            if (filament()->auth()->check()) {
                $model->Usuario = filament()->auth()->id();
            }
        });
    }

    public function getSetorTransferenciaAttribute(): string
    {
        return "{$this->SetorAnterior} -> {$this->SetorAtual}";
    }

    public function getUnidadeTransferenciaAttribute(): string
    {
        return "{$this->UnidadeAnterior} -> {$this->UnidadeAtual}";
    }
}
