<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Request;
use App\Models\Tbl_item;
class HomeController extends Controller
{
    public function get_product_view()
    {
        $item_id        = Request::where('item_id');

        $response       = Tbl_item::where('item_id',$item_id)->first();

        return $response;
    }
}
