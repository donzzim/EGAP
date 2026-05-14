<?php
namespace App\Models\Egap\Patrimonio\BensImoveis;
use Illuminate\Database\Eloquent\Model;

class CidUf extends Model {
    protected $connection = 'egap';
    protected $table = 'imo_ciduf';
    protected $primaryKey = 'id';
    protected $guarded = [];
    public $timestamps = false;
}