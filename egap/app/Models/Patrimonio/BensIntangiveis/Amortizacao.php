<?php

namespace App\Models\Patrimonio\BensIntangiveis;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Amortizacao extends Model
{
    //protected $connection = 'egap';

    protected $table = 'int_amortizacao';

    protected $primaryKey = 'id';

    public $timestamps = false;

    protected $fillable = [
        'date_time',
        'id_intangivel',
        'data_calculo',
        'amortizacao_mensal',
        'amortizacao_acumulada',
        'valor',
        'item',
        'valor_liquido_contabil',
        'vida_util',
    ];

    protected $casts = [
        'date_time' => 'datetime',
        'data_calculo' => 'datetime',
        'amortizacao_mensal' => 'decimal:10',
        'amortizacao_acumulada' => 'decimal:10',
        'valor' => 'decimal:10',
        'valor_liquido_contabil' => 'decimal:10',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $model) {
            $model->date_time ??= now();
        });
    }

    public function idIntangivelRef(): BelongsTo
    {
        return $this->belongsTo(BemIntangivel::class, 'id_intangivel', 'id');
    }
}
