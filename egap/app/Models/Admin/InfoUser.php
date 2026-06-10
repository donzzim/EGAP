<?php

namespace App\Models\Admin;

use App\Models\UserEgap;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InfoUser extends Model
{
    //protected $connection = 'egap';
    protected $table = 'mat_infousers';

    public $timestamps = false;

    protected $fillable = [
        'date_time',
        'usuario_id',
        'cpf',
        'matricula',
        'cargo',
        'no_cnh',
        'validade_cnh',
        'dirigir',
        'contatos',
    ];

    protected $casts = [
        'date_time' => 'datetime',
        'validade_cnh' => 'datetime',
        'dirigir' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(UserEgap::class, 'usuario_id', 'id');
    }

    protected static function booted(): void
    {
        static::saving(function (self $model): void {
            $model->date_time = now();
        });
    }
}
