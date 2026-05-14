<?php

namespace App\Models\Egap\Patrimonio\BensIntangiveis;

use App\Models\UserEgap;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

class TipoBemIntagivel extends Model
{
    //
    protected $connection = 'egap';
    public $timestamps = false;
    protected $table = 'int_tipo';
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
        static::creating(function (self $model) {
            $model->date_time ??= now();

            if (Auth::check()) {
                $model->atualizado_por = Auth::id();
            }
        });

        static::updating(function (self $model) {
            if (Auth::check()) {
                $model->atualizado_por = Auth::id();
            }
        });
    }

}
