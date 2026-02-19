<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use App\Globals\Digima;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;
    
    public function __destruct()
    {
    Digima::addRequest(1);
    }
}
