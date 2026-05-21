<?php

namespace App\Models\Patrimonio\BensMoveis;

use App\Models\Cadastro\Setores;
use App\Models\Patrimonio\Fornecedores;
use App\Models\UserEgap;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Conciliacao extends Model
{
    //protected $connection = 'egap';
    protected $table = 'mat_conciliacao';
    public $timestamps = false;
    public static $snakeAttributes = false;

    protected $fillable = [
        'date_time', 'numero_patrimonio', 'patrimonio_desmembrado', 'descricao',
        'data_aquisicao', 'data_conciliacao', 'local', 'comarca',
        'valor_aquisicao', 'forma_aquisicao', 'numero_documento', 'fornecedor',
        'patrimonio', 'Usuario',
    ];

    /** ✅ RELAÇÕES */
    public function localRef(): BelongsTo { return $this->belongsTo(Setores::class, 'local', 'id'); }
    public function comarcaRef(): BelongsTo { return $this->belongsTo(Setores::class, 'comarca', 'id'); }
    public function fornecedorRef(): BelongsTo { return $this->belongsTo(Fornecedores::class, 'fornecedor', 'id'); }
    public function responsavel(): BelongsTo { return $this->belongsTo(UserEgap::class, 'Usuario', 'id'); }

    protected static function booted()
    {
        static::creating(function ($model) {
            $model->Usuario = auth()->id();
            $model->date_time = now();
        });
    }
}
