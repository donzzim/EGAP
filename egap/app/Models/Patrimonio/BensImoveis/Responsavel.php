<?php
namespace App\Models\Patrimonio\BensImoveis;
use Illuminate\Database\Eloquent\Model;

class Responsavel extends Model {
    //protected $connection = 'egap';
    protected $table = 'imo_responsavel';
    protected $guarded = [];
    public $timestamps = false;
}
