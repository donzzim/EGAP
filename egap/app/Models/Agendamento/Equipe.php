<?php

namespace App\Models\Agendamento;

use App\Models\UserEgap;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Equipe extends Model
{
    //protected $connection = 'egap';
    protected $table = 'age_equipe';

    protected $primaryKey = 'id';

    public $timestamps = false;

    protected $fillable = [
        'date_time',
        'id_user',
        'funcao',
        'id_pessoa',
        'contato',
        'disponivel',
        'ativo',
    ];

    public function idUserRef() : BelongsTo
    {
        return $this->belongsTo(UserEgap::class, 'id_user', 'id');
    }
    public function idPessoaRef() : BelongsTo
    {
        return $this->belongsTo(UserEgap::class, 'id_pessoa', 'id');
    }

    protected static function booted(): void
    {
        static::saving(function (self $model): void {
            $model->date_time = now();
            $model->id_user = auth()->id();
        });
    }
}
