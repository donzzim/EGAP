<?php

namespace App\Models\Agendamento;

use App\Models\Cadastro\Setores;
use App\Models\UserEgap;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Unidades extends Model
{
    //protected $connection = 'egap';
    protected $table = 'age_unidades';

    protected $primaryKey = 'id';

    public $timestamps = false;

    protected $fillable = [
        'date_time',
        'atualizado_por',
        'id_unidade',
        'id_veiculo',
    ];

    public function atualizadoPorRef() : BelongsTo
    {
        return $this->belongsTo(UserEgap::class, 'atualizado_por', 'id');
    }

    public function idUnidadeRef() : BelongsTo
    {
        return $this->belongsTo(Setores::class, 'id_unidade', 'id');
    }

    public function idVeiculoRef() : BelongsTo
    {
        return $this->belongsTo(Frota::class, 'id_veiculo', 'id');
    }

    protected static function booted(): void
    {
        static::saving(function (self $model): void {
            $model->date_time = now();
            $model->atualizado_por = auth()->id();
        });
    }
}
