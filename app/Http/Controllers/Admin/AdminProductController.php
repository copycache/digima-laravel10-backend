<?php
namespace App\Http\Controllers\Admin;

use App\Globals\Code;
use App\Globals\Item;
use App\Models\Tbl_cashier;
use App\Models\Tbl_codes;
use App\Models\Tbl_inventory;
use App\Models\Tbl_item;
use App\Models\Users;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;

class AdminProductController extends AdminController
{
    public function add()
    {
        $response = Item::add(Request::input('item'), Request::input('user'));
        return response()->json($response, $response["status_code"]);
    }

    public function edit()
    {
        $response = Item::edit(Request::input());
        return response()->json($response, $response["status_code"]);
    }

    public function get()
    {
        $response = Item::get_item(Request::input(), 15);
        return response()->json($response, 200);
    }

    public function data()
    {
        $response = Item::get_data(Request::input("id"));
        return response()->json($response, 200);
    }

    public function archive()
    {
        $response = Item::archive(Request::input("id"), Request::input("user"));
        return response()->json($response, 200);
    }

    public function unarchive()
    {
        $response = Item::unarchive(Request::input("id"), Request::input("user"));
        return response()->json($response, 200);
    }

    public function restock()
    {
        $response = Item::restock(Request::input());
        return response()->json($response, $response["status_code"]);
    }

    public function get_inventory()
    {
        $response = Item::get_inventory(Request::input());
        return response()->json($response, 200);
    }

    public function get_item_inventory()
    {
        $response = Item::get_item_inventory(Request::input("id"));
        return response()->json($response, 200);
    }

    public function get_item_code()
    {
        $response = Code::get(Request::input("branch_id"), Request::input(), Request::input("item_id"), 5);
        return response()->json($response);
    }
    public function get_currency()
    {
        $response = Item::get_currency(Request::input());
        return response()->json($response, 200);
    }

    public function generate_codes()
    {
        $item_id = Request::input('item_id');
        $branch_id = Request::input('branch_id');
        $response = Item::check_rel_item_kit($item_id, $branch_id);
        return response()->json($response, 200);
    }
    public function load_island_group()
    {
        $response = Item::load_island_group();
        return response()->json($response, 200);
    }
    public function load_shipping_fee()
    {
        $response = Item::load_shipping_fee();
        return response()->json($response, 200);
    }
    public function get_category_list()
    {
        $response = Item::get_category_list();
        return response()->json($response, 200);
    }
    public function get_subcategory_list()
    {
        $category_id = Request::input('category_id');

        $response = Item::get_subcategory_list($category_id);
        return response()->json($response, 200);
    }
    public function highest_membership_list()
    {
        $response = Item::highest_membership_list();
        return response()->json($response, 200);
    }
    public function load_team_sales_bonus_level()
    {
        $item_id = Request::input('item_id');

        $response = Item::load_team_sales_bonus_level($item_id);
        return response()->json($response, 200);
    }

    public function recount_inventory()
    {
        $inventory = Tbl_inventory::get();
        foreach ($inventory as $key => $value) {
            $available_count = Tbl_codes::where('code_inventory_id', $value->inventory_id)
                ->where('code_sold', 0)
                ->where('code_used', 0)
                ->where('archived', 0)
                ->whereNull('kit_requirement')
                ->count();
            $sold_count = Tbl_codes::where('code_inventory_id', $value->inventory_id)->where('code_sold', 1)->count();
            $total = Tbl_codes::where('code_inventory_id', $value->inventory_id)->count();
            $update['inventory_quantity'] = $available_count;
            $update['inventory_sold'] = $sold_count;
            $update['inventory_total'] = $total;

            Tbl_inventory::where('inventory_id', $value->inventory_id)->update($update);
        }
        $response = 1;
        return response()->json($response);
    }

    public function check_stocks()
    {
        $filters = Request::input();
        $query = Tbl_item::JoinInventory()->JoinBranch()->where('tbl_item.archived', 0);

        if (isset($filters['search']) && $filters['search'] != null) { 
            $query->where("item_sku", "like", "%" . $filters["search"] . "%");
        }
        if (isset($filters['type']) && $filters['type'] != "all") { 
            $query->where("item_type", $filters["type"] );
        }
        if (isset($filters['category']) && $filters['category'] != "all") { 
            $query->where("item_category", $filters["category"] );
        }
        if (isset($filters['sub_category']) && $filters['sub_category'] != "all") { 
            $query->where("item_sub_category", $filters["sub_category"] );
        }
        
        $return = $query->get();

        if ($return->isEmpty()) {
            $return = null;
        }
        return $return;
    }

}
