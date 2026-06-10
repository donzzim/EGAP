<?php
namespace App\Models\Patrimonio\BensImoveis;
use App\Models\UserEgap;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EntradaSaida extends Model {
    //protected $connection = 'egap';
    protected $table = 'imo_entradasaida';
    protected $guarded = ['id'];
    public $timestamps = false;

    public function usuarioRef(): BelongsTo
    {
        return $this->belongsTo(UserEgap::class, 'usuario', 'id');
    }

    protected static function booted(): void
    {
        static::saving(function (self $model): void {
            $model->date_time = now();
            $model->usuario = auth()->id();
        });
    }
}
