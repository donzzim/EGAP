<?php

namespace App\Models\Patrimonio\BensMoveis;

use App\Models\Cadastro\Setores;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AtividadeInventario extends Model
{
    protected $connection = 'egap';
    protected $table = 'inv_atividades';
    public $timestamps = false;

    /** ✅ MAPEAMENTO COMPLETO (Baseado no seu print do phpMyAdmin) */
    protected $fillable = [
        'date_time',
        'id_inventario',
        'id_unidade',
        'setor',
        'complemento',
        'inicio',
        'termino',
        'dupla',
        'situacao',
        'qtde_inventariada'
    ];

    /** ✅ RELAÇÃO: Qual o inventário "pai"? */
    public function inventario(): BelongsTo
    {
        return $this->belongsTo(Inventario::class, 'id_inventario', 'id');
    }

    /** ✅ RELAÇÃO: Unidade Judiciária (mat_setores) */
    public function unidadeRel(): BelongsTo
    {
        return $this->belongsTo(Setores::class, 'id_unidade', 'id');
    }

    /** ✅ RELAÇÃO: Setor específico (mat_setores) */
    public function setorRel(): BelongsTo
    {
        return $this->belongsTo(Setores::class, 'setor', 'id');
    }
}
