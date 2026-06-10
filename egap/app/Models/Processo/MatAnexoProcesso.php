<?php

namespace App\Models\Processo;

use Illuminate\Database\Eloquent\Model;
use App\Models\Patrimonio\BensImoveis\Processo;
use App\Models\Cadastro\DescricaoResumida;

class MatAnexoProcesso extends Model
{
    //protected $connection = 'egap';
    protected $table = 'mat_anexoprocesso';
    protected $primaryKey = 'id';
    public $timestamps = false;
    protected $guarded = [];

    public function processoRelacaoRef()
    {
        return $this->belongsTo(Processo::class, 'num_processo', 'id');
    }

    public function tipoDocumentoRelacaoRef()
    {
        return $this->belongsTo(MatTipoDocumento::class, 'tipo_documento', 'id');
    }

    public function materialRelacaoRef()
    {
        return $this->belongsTo(DescricaoResumida::class, 'material', 'id');
    }

    protected static function booted(): void
    {
        static::saving(function (self $model): void {
            $model->date_time = now();
        });
    }
}
