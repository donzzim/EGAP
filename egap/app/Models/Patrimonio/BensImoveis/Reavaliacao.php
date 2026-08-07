<?php

namespace App\Models\Patrimonio\BensImoveis;

use App\Models\UserEgap;
use Illuminate\Database\Eloquent\Model;

class Reavaliacao extends Model
{
    //protected $connection = 'egap';
    protected $table = 'imo_reavaliacao';
    protected $primaryKey = 'Id';
    protected $guarded = [];
    public $timestamps = false;

    public function imovelRelacaoref()
    {
        return $this->belongsTo(BemImovel::class, 'Id_imovel', 'Id');
    }

    public function estadoConservacaoRelacaoref()
    {
        return $this->belongsTo(EstadoConservacao::class, 'Id_estadoconservacao', 'Id');
    }

    public function atualizadoPorRelacaoref()
    {
        return $this->belongsTo(UserEgap::class, 'atualizado_por', 'id');
    }

    protected static function booted(): void
    {
        static::saving(function (self $model): void {
            $model->date_time = now();
            $model->atualizado_por = auth()->id();
        });
    }
}
