<?php

namespace App\Models\Cadastro;

use App\Models\UserEgap;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Marcas extends Model
{
    //
    protected $connection = 'egap';
    protected $table = 'mat_marca';
    protected $fillable = [
        'date_time',
        'Usuario',
        'descricao',
        'tipobem',
    ];

    public function modelos(): HasMany
    {
        return $this->HasMany(Modelos::class, 'marca', 'id');
    }

    public function atualizado_por() : BelongsTo
    {
        return $this->belongsTo(UserEgap::class, 'Usuario', 'id');
    }

    protected static function booted()
    {
        static::creating(function ($model) {
            $model->Usuario = auth()->id();
        });
    }
}
