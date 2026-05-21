<?php

namespace App\Models\Views;

use Illuminate\Database\Eloquent\Model;

class AtendimentoPedidoView extends Model
{
    //protected $connection = 'egap';

    protected $table = 'ped_atendimentopedidos';

    protected $primaryKey = 'item_id';
    public $incrementing = false;

    public $timestamps = false;

    protected $guarded = [];
}
