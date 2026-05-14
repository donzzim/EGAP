<?php

namespace App\Models\Egap\Views;

use Illuminate\Database\Eloquent\Model;

class MaterialDepositoView extends Model
{
    protected $connection = 'egap';

    protected $table = 'ped_materiaisdeposito';

    protected $primaryKey = 'patrimonio_id';

    public $incrementing = false;

    public $timestamps = false;

    protected $guarded = [];
}
