<?php

namespace App\Models\Patrimonio\BensMoveis;

/** ✅ IMPORTANTE: Agora apontando para a pasta Cadastro */
use App\Models\Cadastro\Setores;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InventarioUnidade extends Model
{
    protected $connection = 'egap';
    protected $table = 'inv_atividades';
    public $timestamps = false;

    protected $fillable = [
        'date_time', 'id_inventario', 'id_unidade', 'setor',
        'complemento', 'inicio', 'termino', 'situacao', 'qtde_inventariada'
    ];

    public function inventario(): BelongsTo
    {
        return $this->belongsTo(Inventario::class, 'id_inventario', 'id');
    }

    /** ✅ RELAÇÃO ATUALIZADA */
    public function unidadeRel(): BelongsTo
    {
        return $this->belongsTo(Setores::class, 'id_unidade', 'id');
    }

    public function setorRel(): BelongsTo
    {
        return $this->belongsTo(Setores::class, 'setor', 'id');
    }

    public function equipes(): HasMany
    {
        return $this->hasMany(InventarioEquipe::class, 'unidade', 'id');
    }
}
