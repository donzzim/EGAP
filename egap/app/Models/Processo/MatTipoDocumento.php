<?php

namespace App\Models\Processo;

use Illuminate\Database\Eloquent\Model;

class MatTipoDocumento extends Model
{
    //protected $connection = 'egap';
    protected $table = 'mat_tipo_documentos';
    protected $primaryKey = 'id';
    public $timestamps = false;
    protected $guarded = [];

    public function anexosRelacaoRef()
    {
        return $this->hasMany(MatAnexoProcesso::class, 'tipo_documento', 'id');
    }
}
