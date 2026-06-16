<?php

namespace App\Models\Patrimonio\BensMoveis;

use App\Models\Cadastro\Setores;
use App\Models\Cadastro\Fornecedores;
use App\Models\UserEgap;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Conciliacao extends Model
{
    //protected $connection = 'egap';
    protected $table = 'mat_conciliacao';
    public $timestamps = false;
    public static $snakeAttributes = false;

    protected $fillable = [
        'date_time',
        'numero_patrimonio',
        'descricao',
        'data_aquisicao',
        'local',
        'comarca',
        'valor_aquisicao',
        'forma_aquisicao',
        'numero_documento',
        'fornecedor',
        'data_conciliacao',
        'patrimonio',
        'patrimonio_desmembrado',
    ];

    public function patrimonioRef(): BelongsTo
    {
        return $this->belongsTo(BemMovel::class, 'patrimonio', 'id');
    }
    protected static function booted(): void
    {
        static::saving(function (self $model): void {
            $model->date_time = now();
        });
    }
}
