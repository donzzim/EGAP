<?php

namespace App\Models\Cadastro;

use App\Models\UserEgap;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DescricaoResumida extends Model
{
    //protected $connection = 'egap';
    protected $table = 'mat_descricaoresumida';
    protected $fillable = [
        'date_time',
        'Descricao',
        'CodigodaClasse',
        'Usuario',
        'ContaContabil',
        'imagem',
        'id_tipo_material',
        'id_produto',
        'visibilidade',
    ];

    protected static function booted(): void
    {
        static::saving(function (self $model): void {
            $model->date_time = now();
            $model->Usuario = auth()->id();
        });
    }

    public function atualizado_por(): BelongsTo
    {
        return $this->belongsTo(UserEgap::class, 'Usuario', 'id');
    }

    public function conta_contabil(): BelongsTo
    {
        return $this->belongsTo(ContaContabil::class, 'ContaContabil', 'id');
    }

    public function produto_id(): BelongsTo
    {
        return $this->belongsTo(ElementoDespesa::class, 'id_produto', 'id');
    }

    public function codigo_da_classe(): BelongsTo
    {
        return $this->belongsTo(ElementoDespesa::class, 'CodigodaClasse', 'id');
    }
}
