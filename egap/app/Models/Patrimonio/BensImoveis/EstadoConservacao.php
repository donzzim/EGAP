<?php
namespace App\Models\Egap\Patrimonio\BensImoveis;
use Illuminate\Database\Eloquent\Model;

class EstadoConservacao extends Model {
    protected $connection = 'egap';
    protected $table = 'imo_estadoconservacao';
    protected $primaryKey = 'Id';
    protected $guarded = [];
    public $timestamps = false;
}