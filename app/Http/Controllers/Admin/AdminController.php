<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use Auth;
use Illuminate\Support\Facades\Request;
use File;
use Storage;
use Illuminate\Support\Facades\DB;

use Aws\S3\S3Client;
use League\Flysystem\AwsS3v3\AwsS3Adapter;
use League\Flysystem\Filesystem;

use App\Globals\Audit_trail;
use App\Globals\Membership;
use App\Globals\Item;
use App\Globals\Slot;
use App\Globals\Wallet;
use App\Globals\Seed;
use App\Globals\Plan;
use App\Models\Tbl_slot;
use App\Models\Tbl_cashier;
use App\Models\Tbl_module_access;
use App\Models\Tbl_mlm_lockdown_plan;
use App\Models\Tbl_mlm_plan;
use App\Models\Tbl_other_settings;
use App\Models\Tbl_label;
use App\Models\Tbl_wallet_log;
use App\Models\Tbl_membership;
use App\Models\Tbl_audit_trail;
use App\Models\User;

use App\Globals\MLM;

use Illuminate\Http\Request as Request2;

class AdminController extends Controller
{
    function __construct()
    {

    }

    public function user_data()
    {
        if(isset(Request::user()->id))
        {
        	if(Request::user()->type == "member")
    		{	
              $check_has_slot = Tbl_slot::where("slot_owner",Request::user()->id)->first();
              if(!$check_has_slot)
              {
                  Slot::create_blank_slot(Request::user()->id);
              }
    		}	
        }

        if(isset(Request::user()->id))
        {
            if(Request::user()->type == "cashier")
            {	
                $check_status = Tbl_cashier::where("cashier_user_id",Request::user()->id)->first();
                
                Request::user()->status = $check_status->cashier_status;
            }	
        }
        
    	return Request::user();
    }

    public function get_membership()
	{
		$membership = Membership::get();

		return response()->json($membership, 200);
	}

	public function get_product()
	{
        $item = Item::get_product();

		return response()->json($item, 200);
    }
    public function get_product_unilevel()
	{
        $item = Item::get_product_unilevel();

		return response()->json($item, 200);
    }
    public function get_product_stairstep()
	{
        $item = Item::get_product_stairstep();

		return response()->json($item, 200);
    }
    public function get_ldautoship()
	{
        $item = Item::get_ldautoship();

		return response()->json($item, 200);
    }
    public function save_product_unilevel()
	{
        $item = Item::save_product_unilevel(Request::input());

		return response()->json($item, 200);
    }
    public function save_ldautoship()
	{
        $item = Item::save_ldautoship(Request::input());

		return response()->json($item, 200);
    }
    public function save_product_stairstep()
	{
        $item = Item::save_product_stairstep(Request::input());

		return response()->json($item, 200);
	}

    public function get_admin_access()
    {
        if(Request::user()->position_id == 0)
        {
            $get  = DB::table('tbl_module')->where('module_type','admin')->get();

            foreach ($get as $key => $value) 
            {
                $response[$value->module_alias] = 0;
            }
        }
        else
        {
            $get  = Tbl_module_access::where('position_id',Request::user()->position_id)->Module()->get();

            foreach ($get as $key => $value) 
            {
                $response[$value->module_alias] = $value->module_access;
            }
        }

        return $response;
    }
    public function audit_login_trail()
    {
        $action = 'Login';
        $old_value = null;
        $new_value = null;
        $user      = Request::user()->id;
        Audit_trail::audit($old_value,$new_value,$user,$action);
    }

    public function get_logo()
    {
        $return = DB::table('tbl_company_details')->first();
        return response()->json($return);
    }
    public function load_lockdown_settings()
    {
        $response = null;
        
        $response["lockdown_enable"] = Tbl_other_settings::where("key","lockdown_enable")->first() ? Tbl_other_settings::where("key","lockdown_enable")->first()->value : 0;

        
        return response()->json($response, 200);
    }

    public function load_lockdown()
    {
        $plans = Tbl_mlm_plan::get();

        foreach ($plans as $key => $value) 
        {
            $check = Tbl_mlm_lockdown_plan::where("mlm_plan_code_id",$value->mlm_plan_id)->first();
            if(!$check)
            {
                Tbl_mlm_lockdown_plan::insert(["mlm_plan_code_id" => $value->mlm_plan_id]);
            }
        }
        $return = Tbl_mlm_lockdown_plan::leftjoin("tbl_mlm_plan","tbl_mlm_plan.mlm_plan_id","tbl_mlm_lockdown_plan.mlm_plan_code_id")
                                    ->leftjoin("tbl_label","tbl_label.plan_code","tbl_mlm_plan.mlm_plan_code")
                                    ->where("mlm_plan_enable",1)
                                    ->select("mlm_plan_code_id","plan_name","is_lockdown_enabled")
                                    ->get();                        
        
        // dd($return);
        return response()->json($return);
    }
    public function load_lockdown_save()
    {
        $old_value = Tbl_mlm_lockdown_plan::get();
        $data = Request::input("data");
        $data_referrence = Tbl_mlm_lockdown_plan::leftjoin("tbl_mlm_plan","tbl_mlm_plan.mlm_plan_id","tbl_mlm_lockdown_plan.mlm_plan_code_id")
                                ->leftjoin("tbl_label","tbl_label.plan_code","tbl_mlm_plan.mlm_plan_code")
                                ->where("mlm_plan_enable",1)
                                ->select("mlm_plan_code_id","plan_name","is_lockdown_enabled")
                                ->get();
        if($data)
        {
            foreach ($data_referrence as $key => $value) 
            {
                foreach ($data as $key => $value2) 
                {
                   if($value["mlm_plan_code_id"] == $value2["mlm_plan_code_id"])
                   {
                        Tbl_mlm_lockdown_plan::where("mlm_plan_code_id",$value2["mlm_plan_code_id"])->update(["is_lockdown_enabled"=>$value2["is_lockdown_enabled"]]);
                   }
                }
            }
            $user      = Request::user()->id;
            $action    = "Edit Lockdown Complan";
            $new_value = Tbl_mlm_lockdown_plan::get();
            Audit_trail::audit(serialize($old_value),serialize($new_value),$user,$action);
            $return["status_code"]    = 200;
            $return["status"]         = "success";
            $return["status_message"] = "Successfully Updated";
        }
        else 
        {
            $return["status_code"]    = 201;
            $return["status"]         = "error";
            $return["status_message"] = "Oopss something went wrong!";
        }

        $update_setting["value"] = Request::input("enable") ? Request::input("enable") : 0;
        $check_settings          = Tbl_other_settings::where("key","lockdown_enable")->first();
        if($check_settings)
        {
            Tbl_other_settings::where("key","lockdown_enable")->update($update_setting);
        }
        else
        {
            Seed::other_settings_seed();
            Tbl_other_settings::where("key","lockdown_enable")->update($update_setting);
        }
       

        return response()->json($return);
    }

    public function get_company_details()
    {
        $return = DB::table('tbl_company_details')->first();

        return response()->json($return);
    }
    public function update_direct_personal_cashback()
	{
		$data 			= Request::input('data');
		$label			= Request::input('label');	
		$old_label 		= Tbl_label::where('plan_code','DIRECT_PERSONAL_CASHBACK')->pluck('plan_name')->first();
		
		Plan::update_label("DIRECT_PERSONAL_CASHBACK",$label);
		Tbl_wallet_log::where('wallet_log_details', $old_label)->update(['wallet_log_details' => $label]);

		foreach($data as $key => $value)
		{
			$new_value  = Tbl_membership::where('membership_id', $value['membership_id'])->first();
			$update['direct_cashback'] = $value['direct_cashback'];

			DB::table('tbl_membership')->where('membership_id', $value['membership_id'])->update($update);

			$old_value  = Tbl_membership::where('membership_id', $value['membership_id'])->first();
			$action     = "Update Direct Personal Cashback";
			$user       = Request::user()->id;
			Audit_trail::audit(serialize($old_value),serialize($new_value),$user,$action);
		}
		$return = Plan::update_status("DIRECT_PERSONAL_CASHBACK",1);

		return response()->json($return);
	}
    public function get_user_details()
	{

		$get_position						= User::where('id',Request::user()->id)->leftjoin('tbl_position','tbl_position.position_id','users.position_id')->first();
		if($get_position)
		{
			$return         				= strtolower($get_position->position_name ?? 'superadmin');
		}
		return response()->json($return);
	}
    public function mlm_feature()
	{

        $return['store_replicated']           =  DB::table('tbl_mlm_feature')->where('mlm_feature_name','store_replicated')->value('mlm_feature_enable');
		return $return;

	}

    public function get_plan_label()
    {
        $labels = Tbl_label::select('plan_code', 'plan_name')->get();

        $plan = $labels->pluck('plan_name', 'plan_code');

        return response()->json($plan);
    }

    public function get_audit_trail()
    {
        $query = Tbl_audit_trail::leftJoin('users', 'users.id', '=', 'tbl_audit_trail.user_id')
            ->select(
                'tbl_audit_trail.*',
                'users.name',
                'users.email',
                'users.first_name',
                'users.last_name'
            )
            ->orderBy('tbl_audit_trail.date_created', 'desc');

        if (Request::input('search')) {
            $search = Request::input('search');
            $query->where(function($q) use ($search) {
                $q->where('tbl_audit_trail.action', 'LIKE', '%' . $search . '%')
                  ->orWhere('users.name', 'LIKE', '%' . $search . '%')
                  ->orWhere('users.email', 'LIKE', '%' . $search . '%');
            });
        }

        if (Request::input('action_filter')) {
            $query->where('tbl_audit_trail.action', Request::input('action_filter'));
        }

        if (Request::input('date_from')) {
            $query->where('tbl_audit_trail.date_created', '>=', Request::input('date_from'));
        }

        if (Request::input('date_to')) {
            $query->where('tbl_audit_trail.date_created', '<=', Request::input('date_to') . ' 23:59:59');
        }

        $result = $query->paginate(20);

        return response()->json($result);
    }

    public function get_audit_actions()
    {
        $actions = Tbl_audit_trail::select('action')
            ->distinct()
            ->orderBy('action')
            ->pluck('action');

        return response()->json($actions);
    }
}
