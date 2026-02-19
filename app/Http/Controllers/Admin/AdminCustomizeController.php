<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use Auth;
use Illuminate\Support\Facades\Request;
use File;
use Storage;
use Illuminate\Support\Facades\DB;

use App\Globals\Audit_trail;

use App\Models\Tbl_genealogy_settings;

class AdminCustomizeController extends Controller
{
    public function get()
	{
        $response = Self::mlm_feature();
		return response()->json($response, 200);
	}

    public static function mlm_feature()
    {
        $return['replicated_member']            =  DB::table('tbl_replicated_settings')->where('replicated_name','membership')->first();
        $return['store_replicated']           =  DB::table('tbl_mlm_feature')->where('mlm_feature_name','store_replicated')->first();
        $return['product_replicated']           =  DB::table('tbl_mlm_feature')->where('mlm_feature_name','product_replicated')->first();
        $return['send_wallet']                  =  DB::table('tbl_mlm_feature')->where('mlm_feature_name','send_wallet')->first();
        $return['conversion_wallet']            =  DB::table('tbl_mlm_feature')->where('mlm_feature_name','conversion_wallet')->first();
        $return['auto_distribute']            =  DB::table('tbl_mlm_feature')->where('mlm_feature_name','auto_distribute')->first();
        $return['code_transfer']            =  DB::table('tbl_mlm_feature')->where('mlm_feature_name','code_transfer')->first();
        $return['add_slot_sponsor_selection'] =  DB::table('tbl_mlm_settings')->first()->add_slot_sponsor_selection;
        $return['add_slot_automatic_sponsor'] =  DB::table('tbl_mlm_settings')->first()->add_slot_automatic_sponsor;
        $return['code_transfer_non']            =  DB::table('tbl_mlm_feature')->where('mlm_feature_name','code_transfer_non')->first();
        return $return;
    }

    public function update()
    {
        $data = Request::all();

        $old['replicated_member']            =  DB::table('tbl_replicated_settings')->where('replicated_name','membership')->first();
        $old['store_replicated']           =  DB::table('tbl_mlm_feature')->where('mlm_feature_name','store_replicated')->first();
        $old['product_replicated']           =  DB::table('tbl_mlm_feature')->where('mlm_feature_name','product_replicated')->first();
        $old['send_wallet']                  =  DB::table('tbl_mlm_feature')->where('mlm_feature_name','send_wallet')->first();
        $old['conversion_wallet']            =  DB::table('tbl_mlm_feature')->where('mlm_feature_name','conversion_wallet')->first();
        $old['auto_distribute']            =  DB::table('tbl_mlm_feature')->where('mlm_feature_name','auto_distribute')->first();
        $old['add_slot_sponsor_selection'] =  DB::table('tbl_mlm_settings')->first()->add_slot_sponsor_selection;
        $old['add_slot_automatic_sponsor'] =  DB::table('tbl_mlm_settings')->first()->add_slot_automatic_sponsor;
        $old['code_transfer_non']            =  DB::table('tbl_mlm_feature')->where('mlm_feature_name','code_transfer_non')->first();
        // dd($data);
        foreach($data as $key => $value)
        {
            if($key == "add_slot_sponsor_selection" || $key == "add_slot_automatic_sponsor")
            {
                if($key == "add_slot_sponsor_selection") {
                    $update_add_slot['add_slot_sponsor_selection'] = $value;
                } else if($key == "add_slot_automatic_sponsor") {
                    $update_add_slot['add_slot_automatic_sponsor'] = $value;
                }
                DB::table('tbl_mlm_settings')->update($update_add_slot);
            } else  if($key == "replicated_member") {
                $updates['replicated_sponsoring'] = $value['replicated_sponsoring'];
                DB::table('tbl_replicated_settings')->where('replicated_id',$value['replicated_id'])->update($updates);
            } else {
                $update['mlm_feature_enable'] = $value['mlm_feature_enable'];
                DB::table('tbl_mlm_feature')->where('mlm_feature_name',$key)->update($update);
            }
        }
        $new['replicated_member']            =  DB::table('tbl_replicated_settings')->where('replicated_name','membership')->first();
        $new['store_replicated']           =  DB::table('tbl_mlm_feature')->where('mlm_feature_name','store_replicated')->first();
        $new['product_replicated']           =  DB::table('tbl_mlm_feature')->where('mlm_feature_name','product_replicated')->first();
        $new['send_wallet']                  =  DB::table('tbl_mlm_feature')->where('mlm_feature_name','send_wallet')->first();
        $new['conversion_wallet']            =  DB::table('tbl_mlm_feature')->where('mlm_feature_name','conversion_wallet')->first();
        $new['auto_distribute']            =  DB::table('tbl_mlm_feature')->where('mlm_feature_name','auto_distribute')->first();
        $new['code_transfer_non']            =  DB::table('tbl_mlm_feature')->where('mlm_feature_name','code_transfer_non')->first();
        
        $action = "Update Customize Settigns";
        $user   = Request::user()->id;
        Audit_trail::audit(serialize($old),serialize($new),$user,$action);

        $response = Self::mlm_feature();

        return response()->json($response, 200);
    }
    public function get_genealogy_data()
	{
        $response = Tbl_genealogy_settings::first();
        if(!$response)
        {
            $update['show_full_name']       = 1;
            $update['show_slot_no']         = 1;
            $update['show_date_joined']     = 1;
            $update['show_directs_no']      = 1;
            $update['show_binary_points']   = 1;
            $update['show_maintenance_pv']  = 1;
            $update['show_sponsor_username']  = 1;
            Tbl_genealogy_settings::insert($update);
        }
		return response()->json($response, 200);
    }
    public function save_genealogy_data()
	{

        $data = Request::input();
        if($data)
        {
            $old = Tbl_genealogy_settings::where("genealogy_settings_id",$data['genealogy_settings_id'])->first();
            $update["show_full_name"]       = $data["show_full_name"];
            $update["show_slot_no"]         = $data["show_slot_no"];
            $update["show_membership"]      = $data["show_membership"];
            $update["show_date_joined"]     = $data["show_date_joined"];
            $update["show_directs_no"]      = $data["show_directs_no"];
            $update["show_binary_points"]   = $data["show_binary_points"];
            $update["show_maintenance_pv"]  = $data["show_maintenance_pv"];
            $update["show_sponsor_username"]  = $data["show_sponsor_username"];
            
            Tbl_genealogy_settings::where("genealogy_settings_id",$data['genealogy_settings_id'])->update($update);
            $new = Tbl_genealogy_settings::where("genealogy_settings_id",$data['genealogy_settings_id'])->first();
            $action = "Edit Genealogy Settings";
            $user   = Request::user()->id;
            Audit_trail::audit(serialize($old),serialize($new),$user,$action);
            $return["status"]             = "success";
            $return["status_code"]        = 100;
            $return["status_message"][0]  = "Successful Updated";
        }
        else
        {
            $return["status"]             = "error";
            $return["status_code"]        = 101;
            $return["status_message"][0]  = "Oops somethings not right!";
        }

		return response()->json($return, 200);
	}




}
