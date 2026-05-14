<?php

namespace App\Models\Egap\Patrimonio\BensMoveis;

use App\Models\Egap\Cadastro\Setores;
use App\Models\Patrimonio\Marcas;
use App\Models\Patrimonio\Modelos;
use App\Models\UserEgap;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ItemInventario extends Model
{
    protected $connection = 'egap';
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

    /** ✅ RELAÇÕES PARA OS SELECTS */
    public function bem(): BelongsTo { return $this->belongsTo(BemMovel::class, 'id_bem', 'id'); }
    public function inventario(): BelongsTo { return $this->belongsTo(Inventario::class, 'id_inventario', 'id'); }
    
    // Relação com Marcas
    public function marcaRel(): BelongsTo { return $this->belongsTo(Marcas::class, 'marca', 'id'); }
    
    // Relação com Modelos
    public function modeloRel(): BelongsTo { return $this->belongsTo(Modelos::class, 'modelo', 'id'); }
    
    // Relação com Setores (Original e Localizado)
    public function setorRef(): BelongsTo { return $this->belongsTo(Setores::class, 'setor', 'id'); }
    public function setorLocalizadoRef(): BelongsTo { return $this->belongsTo(Setores::class, 'setor_localizado', 'id'); }
    
    public function responsavel(): BelongsTo { return $this->belongsTo(UserEgap::class, 'atualizado_por', 'id'); }

    protected static function booted()
    {
        static::creating(function ($model) {
            $model->atualizado_por = auth()->id();
            $model->date_time = now();
        });
    }
}