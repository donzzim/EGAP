<?php

namespace App\Models\Cadastro;

use App\Models\UserEgap;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Fornecedores extends Model
{
    //
    protected $connection = 'egap';
    protected $table = 'mat_fornecedor';
    protected $fillable = [
        'date_time',
        'NomeFornecedor',
        'Pessoa',
        'CNPJ',
        'Usuario'
    ];

    // atualizado por
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
