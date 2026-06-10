<?php

namespace App\Models\Agendamento;

use App\Models\UserEgap;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Recursos extends Model
{
    //protected $connection = 'egap';
    protected $table = 'age_recursos';

    protected $primaryKey = 'id';

    public $timestamps = false;

    protected $fillable = [
        'date_time',
        'id_user',
        'condutor',
        'veiculo',
        'id_solicitacao',
        'observacao',
    ];

    // Não tem nenhuma relação no banco

    protected static function booted(): void
    {
        static::saving(function (self $model): void {
            $model->date_time = now();
            $model->id_user = auth()->id();
        });
    }
}
