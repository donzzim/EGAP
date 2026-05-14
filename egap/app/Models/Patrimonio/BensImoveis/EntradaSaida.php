<?php
namespace App\Models\Patrimonio\BensImoveis;
use Illuminate\Database\Eloquent\Model;

class EntradaSaida extends Model {
    protected $connection = 'egap';
    protected $table = 'imo_entradasaida';
    protected $guarded = [];
    public $timestamps = false;
}
