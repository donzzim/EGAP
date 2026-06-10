<?php

namespace App\Models\Patrimonio\BensIntangiveis;

use App\Models\UserEgap;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

class Fabricante extends Model
{
    //
    //protected $connection = 'egap';
    public $timestamps = false;
    protected $table = 'int_fabricante';
    protected $fillable = [
        'descricao',
        'atualizado_por',
        'date_time',
    ];

    public function atualizadoPorRef() : BelongsTo
    {
        return $this->belongsTo(UserEgap::class, 'atualizado_por', 'id');
    }

    protected static function booted(): void
    {
        static::saving(function (self $model): void {
            $model->date_time = now();
            $model->atualizado_por = auth()->id();
        });
    }
}
