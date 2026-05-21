<?php

namespace App\Models\Patrimonio\BensMoveis;

use App\Models\UserEgap;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Reavaliacao extends Model
{
    //protected $connection = 'egap';
    protected $table = 'mat_reavaliacao'; // ✅ Nome provável baseado no padrão
    public $timestamps = false;
    public static $snakeAttributes = false;

    protected $fillable = [
        'id_patrimonio',
        'data_reavaliacao',
        'data_disponibilizacao',
        'data_referencia',
        'valor_aquisicao',
        'vida_util_reavaliacao',
        'vida_util_siafi',
        'vida_util',
        'tempo_utilizacao_meses',
        'vida_util_remanescente_meses',
        'valor_mercado',
        'utilizacao_bem_anos',
        'vida_util_estimada_anos',
        'estado_conservacao',
        'pub1',
        'puv',
        'fr',
        'valor_reavaliacao',
        'ajuste_contabil',
        'date_time',
        'Usuario',
    ];

    /** ✅ RELAÇÕES PARA CONSULTA */
    public function bem(): BelongsTo { return $this->belongsTo(BemMovel::class, 'id_patrimonio', 'id'); }
    public function responsavel(): BelongsTo { return $this->belongsTo(UserEgap::class, 'Usuario', 'id'); }
}
