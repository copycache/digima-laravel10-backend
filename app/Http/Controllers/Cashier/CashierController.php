<?php
namespace App\Http\Controllers\Cashier;
use App\Http\Controllers\Controller;
use App\Models\Tbl_slot;
use Auth;
use Illuminate\Support\Facades\Request;
use App\Models\Users;
use App\Models\Tbl_cashier;
use App\Models\Tbl_codes;
use App\Models\Tbl_receipt;

use Illuminate\Support\Facades\DB;
use Crypt;
use Hash;

use Illuminate\Http\Request as Request2;

class CashierController extends Controller
{
    function __construct()
    {

    }

    public function get_cashier_info()
    {
    	$response = Request::user();
    	$response->decrypted = Crypt::decryptString(Request::user()->crypt);
    	return response()->json($response);
    }
    public function update_cashier_info()
    {
        $message = "success";

        if(Request::input('email')!= Request::user()->email)
        {
            $check_email = Users::where('email',Request::input('email'))->count();
            if($check_email!=0)
            {
                $message = "EMAIL ALREADY EXIST";  
            }
            else
            {
                $update["email"]            = Request::input('email');
            }
        }

        $decrypted = Crypt::decryptString(Request::user()->crypt);
        // dd(Request::input('decrypted'),$decrypted);

        if(Request::input('decrypted')!= $decrypted)
        {
            $update["password"]         = Hash::make(Request::input('decrypted'));
            $update["crypt"]            = Crypt::encryptString(Request::input('decrypted'));
        }
        $update["first_name"]	    = Request::input('first_name');
		$update["last_name"]		= Request::input('last_name');
		$update["gender"]			= Request::input('gender');
		$update["birthdate"]		= Request::input('birthdate');
		$update["name"]			    = Request::input('first_name')." ".Request::input('last_name');


        if($message=="success")
        {
            Users::where('id',Request::user()->id)->update($update);
        }
        return response()->json($message);
    }

    public function load_company_info()
    {
        $return = DB::table('tbl_company_details')->first();

        return response()->json($return);
    }

    public function sales_receipt()
    {
        $receipt_id = Request::input('id');

        $return = Tbl_receipt::where('receipt_id', $receipt_id)
        ->join('tbl_receipt_rel_item', 'tbl_receipt_rel_item.rel_receipt_id', '=', 'tbl_receipt.receipt_id')
        ->join('tbl_orders', 'tbl_orders.order_id', '=', 'tbl_receipt.receipt_order_id')
        ->join('tbl_item', 'tbl_item.item_id', '=', 'tbl_receipt_rel_item.item_id')
        ->join('tbl_cashier_payment_method', 'tbl_cashier_payment_method.cashier_payment_method_id', '=', 'tbl_receipt.payment_method')
        ->get();
       

        foreach ($return as $key => $value) 
		{
            $slot_info = Tbl_slot::where("slot_id", $value->buyer_slot_id)->Owner()->first();
            $code_info = [];
            $items = json_decode($value->items);
            foreach($items as $i => $item) {
                // Fetch the code and pin in a single query
                if($key == $i) {
                    $codeDetails = Tbl_codes::where("code_inventory_id", $item->item_id)
                    ->where("code_sold_to", $slot_info->id)
                    ->where("code_date_sold", $value->receipt_date_created)
                    ->get();
                    
                    if ($codeDetails) {
                        foreach($codeDetails as $j => $code) {
                            // dd($code);
                            $code_info[$j]["code"] = $code->code_activation;
                            $code_info[$j]["pin"] = $code->code_pin;
                        }
                    }
                }   
                // dd( $code);
            }  
			$return[$key]['code_info'] = $code_info;
			$return[$key]['slot'] = $slot_info;

            // $return[$key]['code'] = Tbl_codes::where("code_sold_to", $value->buyer_slot_id)->first();
			$discounted_price = json_decode($value->discount);
			$return[$key]['discounted_price'] = $discounted_price[$key]->original_price - $discounted_price[$key]->percentage;
		}
        return response()->json($return);
    }
    
    public function get_receipt_details()
    {
        $return = DB::table('tbl_receipt_details')->first();

        return response()->json($return);
    }
}
