<?php

namespace App\Http\Controllers;

use App\Models\Tbl_item;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function get_product_view(Request $request)
    {
        return Tbl_item::find($request->input('item_id'));
    }
}
