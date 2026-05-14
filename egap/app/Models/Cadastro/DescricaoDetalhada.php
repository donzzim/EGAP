<?php

namespace App\Models\Cadastro;

use App\Models\Almoxarifado\NotaFiscal;
use App\Models\UserEgap;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DescricaoDetalhada extends Model
{
    protected $connection = 'egap';

    protected $table = 'mat_descricaodetalhada';

    public $timestamps = false;

    protected $fillable = [
        'date_time',
        'descricao_resumida',
        'descricao_detalhada',
        'marca',
        'modelo',
        'valor_mercado',
        'atualizado_por',
        'imagem',
        'visibilidade',
        'pdf',
        'unidade_medida',
        'item_estoque',
    ];

    protected $casts = [
        'valor_mercado' => 'decimal:6',
        'date_time' => 'datetime',
    ];

    protected static function booted()
    {
        static::creating(function ($model) {
            $model->atualizado_por = auth()->id();
            $model->date_time = now();
        });
    }

    public function marca_text() : BelongsTo
    {
        return $this->belongsTo(Marcas::class, 'marca', 'id');
    }

    public function modelo_text() : BelongsTo
    {
        return $this->belongsTo(Modelos::class, 'modelo', 'id');
    }

    public function descricao_resumida_text(): BelongsTo
    {
        return $this->belongsTo(DescricaoResumida::class, 'descricao_resumida', 'id');
    }

    public function unidadeMedida() : BelongsTo
    {
        return $this->belongsTo(UnidadesDeMedida::class, 'unidade_medida', 'id');
    }

    public function atualizado_por_usuario(): BelongsTo
    {
        return $this->belongsTo(UserEgap::class, 'atualizado_por', 'id');
    }

    public function notaFiscal() : HasMany
    {
        return $this->hasMany(NotaFiscal::class, 'id_notafiscal', 'id');
    }
}
