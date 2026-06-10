<?php

namespace App\Models\Processo;

use Illuminate\Database\Eloquent\Model;
use App\Models\Patrimonio\BensImoveis\Processo;

class MatTipoProcesso extends Model
{
    //protected $connection = 'egap';
    protected $table = 'mat_tipo_processo';
    protected $primaryKey = 'id';
    public $timestamps = false;
    protected $guarded = [];

    public function processosRelacaoRef()
    {
        return $this->hasMany(Processo::class, 'id_tipo_processo', 'id');
    }

    protected static function booted(): void
    {
        static::saving(function (self $model): void {
            $model->date_time = now();
        });
    }
}
