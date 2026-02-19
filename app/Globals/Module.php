<?php
namespace App\Globals;

use Illuminate\Support\Facades\DB;
use App\Models\Tbl_module;
use App\Models\Tbl_slot;
use App\Globals\CashOut;
class Module
{
    public static function get_module($is_active,$slot_id = 0)
    {
        $response = Self::module_settings($is_active,$slot_id);
        return $response;
    }


    public static function module_settings($is_active,$slot_id = 0)
    {
        $c_active = CashOut::cashout_settings();
        $module = Tbl_module::where('module_type','member')->get();


        if($slot_id)
        {
            $slot = Tbl_slot::where("slot_id",$slot_id)->first();
            if($slot)
            {
                if($slot->is_retailer == 1)
                {
                   $allowed_module = ["mywallet","cashin","eloading"]; 
                   $module         = Tbl_module::where('module_type','member')->whereIn("module_alias",$allowed_module)->get(); 
                }
            }
        }


        foreach ($module as $key => $value) 
        {
            if($value->module_alias == "cashout")
            {
                if($is_active == 0)
                {
                    if($c_active == 0 && $value->module_is_enable == 0)
                    {
                        $response[$value->module_alias] = $value->module_is_enable;
                    }
                    else
                    {
                        $response[$value->module_alias] = 1;
                    }
                }
                else
                {
                    if($c_active == 0 && $value->slot_is_enable == 0)
                    {
                        $response[$value->module_alias] = $value->slot_is_enable;
                    }
                    else
                    {
                        $response[$value->module_alias] = 1;
                    }
                }
                
            }
            else
            {
                $response[$value->module_alias] = $is_active == 0 ? $value->module_is_enable : $value->slot_is_enable;
            }
        }



        $response['replicated_member']            =  DB::table('tbl_replicated_settings')->where('replicated_name','membership')->value('replicated_sponsoring');
        $response['send_wallet']                  =  DB::table('tbl_mlm_feature')->where('mlm_feature_name','send_wallet')->value('mlm_feature_enable');
        $response['conversion_wallet']            =  DB::table('tbl_mlm_feature')->where('mlm_feature_name','conversion_wallet')->value('mlm_feature_enable');
        $response['product_replicated']           =  DB::table('tbl_mlm_feature')->where('mlm_feature_name','product_replicated')->value('mlm_feature_enable');
        $response['store_replicated']             =  DB::table('tbl_mlm_feature')->where('mlm_feature_name','store_replicated')->value('mlm_feature_enable');
        $response['code_transfer']                =  DB::table('tbl_mlm_feature')->where('mlm_feature_name','code_transfer')->value('mlm_feature_enable');
        $response['code_transfer_non']            =  DB::table('tbl_mlm_feature')->where('mlm_feature_name','code_transfer_non')->value('mlm_feature_enable');
        return $response;   
    }   
}
