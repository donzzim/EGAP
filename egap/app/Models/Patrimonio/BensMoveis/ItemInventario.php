<?php

namespace App\Models\Patrimonio\BensMoveis;

use App\Models\Cadastro\Setores;
use App\Models\Cadastro\Marcas;
use App\Models\Cadastro\Modelos;
use App\Models\UserEgap;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ItemInventario extends Model
{
    //protected $connection = 'egap';
    protected $table = 'mat_itensinventario';
    protected $primaryKey = 'id';
    public $timestamps = false;
    public static $snakeAttributes = false;

    protected $fillable = [
        'date_time', 'id_bem', 'unidades', 'num_patrimonio', 'num_patrimonioantigo',
        'num_serie', 'descricao_resumida', 'marca', 'modelo', 'setor', 'id_inventario',
        'estado_conservacao', 'setor_localizado', 'unidade_localizado', 'complemento_localizado',
        'descricao_detalhada', 'observacao', 'situacao', 'termo', 'atualizado_por',
        'num_serie_egap', 'descricao_detalhada_egap', 'marca_egap', 'modelo_egap',
        'id_complementosetor', 'transferido_em', 'conciliado_patrimonio', 'imagem_enviada'
    ];

    protected $casts = [
        'date_time' => 'datetime',
        'transferido_em' => 'datetime',
    ];

    public function idInventarioRef(): BelongsTo
    {
        return $this->belongsTo(Inventario::class, 'id_inventario', 'id');
    }

    public function idBemRef(): BelongsTo
    {
        return $this->belongsTo(BemMovel::class, 'id_bem', 'id');
    }

    public function unidadesRef(): BelongsTo
    {
        return $this->belongsTo(Setores::class, 'unidades', 'id');
    }

    public function scopeDoInventario(Builder $query, Inventario|int $inventario): Builder
    {
        $inventarioId = $inventario instanceof Inventario ? $inventario->getKey() : $inventario;

        return $query->where('id_inventario', $inventarioId);
    }

    public function scopeDoSetor(Builder $query, int $setor): Builder
    {
        return $query->where('setor', $setor);
    }

    protected static function booted(): void
    {
        static::saving(function (self $model): void {
            $model->date_time = now();
            $model->atualizado_por = auth()->id();
        });
    }
}
