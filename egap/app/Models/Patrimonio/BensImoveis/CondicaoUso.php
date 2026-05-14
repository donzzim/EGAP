<?php
namespace App\Models\Patrimonio\BensImoveis;
use Illuminate\Database\Eloquent\Model;

class CondicaoUso extends Model {
    protected $connection = 'egap';
    protected $table = 'imo_condicaouso';
    protected $guarded = [];
    public $timestamps = false;
}
