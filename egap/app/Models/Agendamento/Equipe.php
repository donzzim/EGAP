<?php

namespace App\Models\Egap\Agendamento;

use App\Models\UserEgap;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Equipe extends Model
{
    protected $connection = 'egap';
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

    protected static function booted()
    {
        static::creating(function ($model) {
            $model->id_user = auth()->id();
            $model->date_time = now();
        });

        static::updating(function ($model) {
            $model->id_user = auth()->id();
            $model->date_time = now();
        });
    }
}
