<?php

namespace App\Models\Patrimonio\BensMoveis;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\UserEgap;
use Illuminate\Support\Facades\Auth;

class Baixa extends Model
{
    //protected $connection = 'egap';
    protected $table = 'mat_baixa';
    public $timestamps = false;

    protected $fillable = [
        'date_time',
        'Usuario',
        'NumeroProcesso',
        'DataBaixa',
        'Requisitante',
        'RequisitanteCnpj',
        'Observacao',
        'Endereco',
    ];

    /**
     * ✅ GATILHO DE CRIAÇÃO:
     * Grava automaticamente o usuário e a data/hora.
     */
    protected static function booted(): void
    {
        static::saving(function (self $model): void {
            $model->date_time = now();
            $model->Usuario = auth()->id();
        });
    }

    public function itens(): HasMany
    {
        return $this->hasMany(ItemBaixa::class, 'id_baixa', 'id');
    }

    /**
     * ✅ RELAÇÃO PADRONIZADA: responsavelRef
     */
    public function responsavelRef(): BelongsTo
    {
        return $this->belongsTo(UserEgap::class, 'Usuario', 'id');
    }
}
