<?php
namespace App\Models\Patrimonio\BensImoveis;
use Illuminate\Database\Eloquent\Model;

class Situacao extends Model {
    //protected $connection = 'egap';
    protected $table = 'imo_situacao';
    protected $primaryKey = 'Id';
    protected $guarded = [];
    public $timestamps = false;
}
