<?php

namespace App\Models\Patrimonio\BensImoveis;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Processo extends Model
{
    //protected $connection = 'egap';

    protected $table = 'mat_processos';

    protected $primaryKey = 'id';

    public $timestamps = false;

    protected $guarded = [];

    public function tipoProcessoRelacaoRef() : BelongsTo
    {
        return $this->belongsTo(\App\Models\Processo\MatTipoProcesso::class, 'id_tipo_processo', 'id');
    }

    public function unidadeRequisitanteRelacaoRef() : BelongsTo
    {
        return $this->belongsTo(\App\Models\Cadastro\Setores::class, 'unidade_demandante', 'id');
    }

    public function processoPaiRelacaoRef() : BelongsTo
    {
        return $this->belongsTo(self::class, 'id_processo_pai', 'id');
    }

    public function fornecedorRelacaoRef() : BelongsTo
    {
        return $this->belongsTo(\App\Models\Cadastro\Fornecedores::class, 'id_fornecedor', 'id');
    }

    public function gestorTitularRelacaoRef() : BelongsTo
    {
        return $this->belongsTo(\App\Models\UserEgap::class, 'gestor_titular', 'id');
    }

    public function gestorSubstitutoRelacaoRef() : BelongsTo
    {
        return $this->belongsTo(\App\Models\UserEgap::class, 'gestor_substituto', 'id');
    }

    public function documentacoesRelacaoRef() : HasMany
    {
        return $this->hasMany(\App\Models\Processo\MatAnexoProcesso::class, 'num_processo', 'id');
    }

    public function materiaisRelacaoRef() : HasMany
    {
        return $this->hasMany(\App\Models\Processo\ProMaterial::class, 'processo', 'id');
    }
}
