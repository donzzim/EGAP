<?php

namespace App\Models\Almoxarifado;

use App\Models\UserEgap;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TipoMovimentacaoNotaFiscal extends Model
{
    //
    //protected $connection = 'egap';
    public $timestamps = false;
    protected $table = 'alm_tipo_movimentacao';
    protected $fillable = [
        'date_time',
        'descricao',
        'atualizado_por',
    ];

    public function atualizadoPor() : BelongsTo
    {
        return $this->belongsTo(UserEgap::class, 'atualizado_por', 'id');
    }

    protected static function booted(): void
    {
        static::saving(function (self $model): void {
            $model->date_time = now();
            $model->atualizado_por = auth()->id();
        });
    }
}
