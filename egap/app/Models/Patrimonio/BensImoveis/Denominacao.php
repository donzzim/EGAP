<?php
namespace App\Models\Egap\Patrimonio\BensImoveis;
use Illuminate\Database\Eloquent\Model;

class Denominacao extends Model {
    protected $connection = 'egap';
    protected $table = 'imo_denominacao';
    protected $guarded = [];
    public $timestamps = false;
}