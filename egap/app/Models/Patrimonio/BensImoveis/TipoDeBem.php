<?php
namespace App\Models\Patrimonio\BensImoveis;
use Illuminate\Database\Eloquent\Model;

class TipoDeBem extends Model {
    //protected $connection = 'egap';
    protected $table = 'imo_tipodebem';
    protected $primaryKey = 'Id';
    protected $guarded = [];
    public $timestamps = false;
}
