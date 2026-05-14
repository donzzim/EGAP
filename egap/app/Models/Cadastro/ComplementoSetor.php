<?php

namespace App\Models\Egap\Cadastro;

use App\Models\UserEgap;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ComplementoSetor extends Model
{
    protected $connection = 'egap';
    public $timestamps = false;
    protected $table = 'mat_complementosetor';
    protected $fillable = [
        'date_time',
        'descricao',
        'Usuario',
    ];

    public function atualizado_por() : BelongsTo
    {
        return $this->belongsTo(UserEgap::class, 'Usuario', 'id');
    }

    protected static function booted()
    {
        static::creating(function ($model) {
            $model->Usuario = auth()->id();
            $model->date_time = now();
        });
    }
}
