<?php

namespace App\Models\Patrimonio\BensImoveis;

use Illuminate\Database\Eloquent\Model;

class Cidades extends Model
{
    protected $connection = 'egap';
    protected $table = 'imo_cidade';
    protected $fillable = [
        'id',
        'descricao',
    ];
}
