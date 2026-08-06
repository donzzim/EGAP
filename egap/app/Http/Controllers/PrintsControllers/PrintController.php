<?php

namespace App\Http\Controllers\PrintsControllers;

use App\Http\Controllers\Controller;

abstract class PrintController extends Controller
{
    abstract function print(int $id);
}
