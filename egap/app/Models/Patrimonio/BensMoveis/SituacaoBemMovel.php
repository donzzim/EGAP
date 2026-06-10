<?php

namespace App\Models\Patrimonio\BensMoveis;

use App\Models\UserEgap;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

class SituacaoBemMovel extends Model
{
    //protected $connection = 'egap';
    protected $table = 'mat_situacao';
    public $timestamps = false;

    protected $fillable = [
        'date_time',
        'descricao',
        'Usuario',
        'situacao'
    ];

    /**
     * ✅ ACCESSOR: Formata a exibição como "Descrição/Situação"
     * Ex: "Ativo/Ativo" ou "Doação/Baixado"
     */
    public function getDescricaoCompletaAttribute(): string
    {
        return "{$this->descricao}/{$this->situacao}";
    }

    public function usuarioRef(): BelongsTo
    {
        return $this->belongsTo(UserEgap::class, 'Usuario', 'id');
    }

    protected static function booted(): void
    {
        static::saving(function (self $model): void {
            $model->date_time = now();
            $model->Usuario = auth()->id();
        });
    }
}
