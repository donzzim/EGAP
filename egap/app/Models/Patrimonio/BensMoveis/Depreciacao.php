<?php

namespace App\Models\Patrimonio\BensMoveis;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Depreciacao extends Model
{
    //protected $connection = 'egap';
    protected $table = 'mat_depreciacao'; //
    public $timestamps = false;

    protected $fillable = [
        'date_time',
        'patrimonio',
        'item',
        'data_calculo',
        'valor',
        'vida_util',
        'valor_residual',
        'depreciacao_mensal',
        'depreciacao_acumulada',
        'valor_liquido_contabil',
    ];

    /** ✅ RELAÇÃO: Liga ao Bem Móvel para pegar o número oficial */
    public function bem(): BelongsTo
    {
        return $this->belongsTo(BemMovel::class, 'patrimonio', 'id');
    }
}
