<?php

namespace App\Models\Egap\Cadastro;

use App\Models\UserEgap;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SituacaoBem extends Model
{
    protected $connection = 'egap';
    protected $table = 'mat_situacao';
    protected $fillable = [
        'descricao',
        'Usuario',
        'situacao',
    ];

    public function atualizado_por() : BelongsTo
    {
        return $this->belongsTo(UserEgap::class, 'Usuario', 'id');
    }
}
