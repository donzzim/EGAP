<?php
namespace App\Models\Egap\Patrimonio\BensImoveis;
use Illuminate\Database\Eloquent\Model;

class TipoImovel extends Model {
    protected $connection = 'egap';
    protected $table = 'imo_tipoimovel';
    protected $primaryKey = 'Id';
    protected $guarded = [];
    public $timestamps = false;
}