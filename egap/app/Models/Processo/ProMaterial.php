<?php

namespace App\Models\Processo;

use App\Models\Patrimonio\BensImoveis\Processo;
use App\Models\Cadastro\DescricaoDetalhada;
use App\Models\UserEgap;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProMaterial extends Model
{
    protected $connection = 'egap';

    protected $table = 'pro_materiais';

    protected $primaryKey = 'id';

    public $timestamps = false;

    protected $guarded = [];

    protected $fillable = ['processo'];

    protected $casts = [
        'date_time' => 'datetime',
        'qtde_min' => 'integer',
        'qtde_max' => 'integer',
        'preco' => 'decimal:2',
        'saldo_atual' => 'decimal:2',
    ];

    public function processoRelacaoRef(): BelongsTo
    {
        return $this->belongsTo(Processo::class, 'processo', 'id');
    }

    public function materialRelacaoRef(): BelongsTo
    {
        return $this->belongsTo(DescricaoDetalhada::class, 'material', 'id');
    }

    public function atualizadoPorRelacaoRef(): BelongsTo
    {
        return $this->belongsTo(UserEgap::class, 'atualizado_por', 'id');
    }
}
