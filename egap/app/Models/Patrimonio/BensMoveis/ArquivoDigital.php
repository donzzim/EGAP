<?php

namespace App\Models\Patrimonio\BensMoveis;

use App\Models\UserEgap;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ArquivoDigital extends Model
{
    //protected $connection = 'egap';
    protected $table = 'mat_arquivodigital';
    public $timestamps = false;

    protected $fillable = [
        'date_time',
        'termo',
        'arquivo_digital',
        'atualizado_em',
        'atualizado_por',
        'data_validacao',
        'validado_por',
        'observacao',
        'situacao', // SELECT: 0 => ['Pendente', 1 => 'Validado', 2 => 'Rejeitado', default => 'Indefinido']
        'web',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relacionamentos
    |--------------------------------------------------------------------------
    */

    public function termoRel(): BelongsTo
    {
        return $this->belongsTo(Termo::class, 'termo', 'id');
    }

    public function atualizadoPor(): BelongsTo
    {
        return $this->belongsTo(UserEgap::class, 'atualizado_por', 'id');
    }

    public function validadoPor(): BelongsTo
    {
        return $this->belongsTo(UserEgap::class, 'validado_por', 'id');
    }

    protected static function booted(): void
    {
        static::creating(function (self $model) {

            if (blank($model->date_time)) {
                $model->date_time = now();
            }

            $model->atualizado_em = now();

            if (filament()->auth()->check()) {
                $model->atualizado_por = filament()->auth()->id();
            }
        });

        static::updating(function (self $model) {

            $model->atualizado_em = now();

            if (filament()->auth()->check()) {
                $model->atualizado_por = filament()->auth()->id();
            }
        });
    }
}
