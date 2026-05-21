<?php

namespace App\Models\Processo;

use Illuminate\Database\Eloquent\Model;

class OrcProjeto extends Model
{
    //protected $connection = 'egap';
    protected $table = 'orc_projeto';
    protected $primaryKey = 'id';
    protected $guarded = [];
    public $timestamps = false;
}
