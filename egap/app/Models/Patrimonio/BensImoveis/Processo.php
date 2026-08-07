<?php

namespace App\Models\Patrimonio\BensImoveis;

use App\Models\Processo\MatTipoProcesso;
use App\Models\Cadastro\Setores;
use App\Models\Cadastro\Fornecedores;
use App\Models\UserEgap;
use App\Models\Processo\MatAnexoProcesso;
use App\Models\Processo\ProMaterial;
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
        return $this->belongsTo(MatTipoProcesso::class, 'id_tipo_processo', 'id');
    }

    public function unidadeRequisitanteRelacaoRef() : BelongsTo
    {
        return $this->belongsTo(Setores::class, 'unidade_demandante', 'id');
    }

    public function processoPaiRelacaoRef() : BelongsTo
    {
        return $this->belongsTo(self::class, 'id_processo_pai', 'id');
    }

    public function fornecedorRelacaoRef() : BelongsTo
    {
        return $this->belongsTo(Fornecedores::class, 'id_fornecedor', 'id');
    }

    public function gestorTitularRelacaoRef() : BelongsTo
    {
        return $this->belongsTo(UserEgap::class, 'gestor_titular', 'id');
    }

    public function gestorSubstitutoRelacaoRef() : BelongsTo
    {
        return $this->belongsTo(UserEgap::class, 'gestor_substituto', 'id');
    }

    public function documentacoesRelacaoRef() : HasMany
    {
        return $this->hasMany(MatAnexoProcesso::class, 'num_processo', 'id');
    }

    public function materiaisRelacaoRef() : HasMany
    {
        return $this->hasMany(ProMaterial::class, 'processo', 'id');
    }

    protected static function booted(): void
    {
        static::saving(function (self $model): void {
            $model->date_time = now();
        });
    }
}
