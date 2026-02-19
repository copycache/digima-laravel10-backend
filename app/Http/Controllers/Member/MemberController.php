<?php
namespace App\Http\Controllers\Member;
use App\Http\Controllers\Controller;
use App\Models\Tbl_cash_out_settings;
use App\Models\Tbl_welcome_bonus_commissions;
use Auth;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\DB;
use Storage;
use Crypt;
use Hash;
use Illuminate\Support\Facades\Validator;
use stdClass;

use App\Models\Tbl_earning_log;
use App\Models\Tbl_mlm_plan;
use App\Models\Tbl_wallet;
use App\Models\Tbl_currency;
use App\Models\Tbl_service_charge;
use App\Models\Tbl_cash_in_proofs;
use App\Models\Tbl_slot;
use App\Models\Tbl_tree_sponsor;
use App\Models\Tbl_tree_placement;
use App\Models\Tbl_wallet_log;
use App\Models\Tbl_points_log;
use App\Models\Tbl_mlm_unilevel_settings;
use App\Models\Tbl_membership;
use App\Models\Tbl_binary_points;
use App\Models\Tbl_unilevel_points;
use App\Models\Tbl_stairstep_points;
use App\Models\Tbl_stairstep_rank;
use App\Models\Tbl_unilevel_or_points;
use App\Models\Tbl_membership_unilevel_or_level;
use App\Models\Tbl_leveling_bonus_points;
use App\Models\Tbl_other_settings;
use App\Models\Tbl_codes;
use App\Models\Tbl_cash_out_list;
use App\Models\Tbl_binary_settings;
use App\Models\Users;
use App\Models\Tbl_membership_upgrade_settings;
use App\Models\Tbl_user_process;
use App\Models\Tbl_lockdown_autoship_items;
use Aws\S3\S3Client;
use League\Flysystem\AwsS3v3\AwsS3Adapter;
use League\Flysystem\Filesystem;
use App\Models\Tbl_address;
use App\Models\Tbl_orders_for_approval;
use App\Models\Tbl_label;
use App\Models\Tbl_unilevel_matrix_bonus_settings;
use App\Models\Tbl_mlm_settings;
use App\Models\Tbl_binary_projected_income_log;
use App\Models\Tbl_prime_refund_points_log;
use App\Models\Tbl_milestone_bonus_settings;
use App\Models\Tbl_milestone_points_log;
use App\Models\Tbl_marketing_support_log;
use App\Models\Tbl_marketing_support_settings;
use App\Models\Tbl_slot_limit;
use App\Models\Tbl_leaders_support_settings;
use App\Models\Tbl_leaders_support_log;

use App\Globals\Code;
use App\Globals\Mlm_complan_manager;
use App\Globals\Log;
use App\Globals\Slot;
use App\Globals\Slot_create;
use App\Globals\Wallet;
use App\Globals\CashIn;
use App\Globals\CashOut;
use App\Globals\Currency;
use App\Globals\Module;
use App\Globals\Member;
use Carbon\Carbon;
use App\Globals\Investment;
use App\Globals\User_process;
use App\Globals\MLM;
use App\Globals\Special_plan;
use App\Globals\Product;


use Illuminate\Http\Request as Request2;

class MemberController extends Controller
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

    	return Request::user();
    }

    public function wallet_log()
    {
        Investment::load_package();
        $var = Request::input();
        $slot                    = Tbl_slot::where("slot_owner",Request::user()->id)->where("slot_id",$var['slot_id'])->first();
        $data                    = null;
        $running_balance         = 0;
        $currency_default        = Tbl_currency::where("currency_default",1)->first();
        if($currency_default)
        {
            $currency_id = $currency_default->currency_id;
        }
        else
        {
            $currency_id = null;
        }
 

        if($slot)
        {
            $data = Tbl_wallet_log::label()->WalletLog($slot->slot_id);
                                    
            if($var['currency'] != 'all')
            {
                $data->where("currency_id", $var['currency']);
            }
            else
            {
                $currency_id = [16,17]; 
                $data->where("currency_id",'!=',$currency_id);
            }

            if($var['month'] != 'all')
            {
                $data->whereMonth('wallet_log_date_created', $var['month']);
            }

            if($var['year'] != 'all')
            {
                $data->whereYear('wallet_log_date_created', $var['year']);
            }

            $data = $data->orderBy('wallet_log_date_created', 'DESC')
                    ->orderBy('wallet_log_id', 'desc')
                    ->paginate(15);

            foreach ($data as $key => $value) 
            {
                $value['currency_info'] = Tbl_currency::where('currency_id',$value->currency_id)->first();
            }
        }
        
        // $wallet_log                 = Tbl_wallet_log::where("wallet_log_slot_id",$slot->slot_id)->where("currency_id",$currency_id)->orderBy("wallet_log_id","DESC")->first();
        // $count_log                  = count($data);
        // $data["total_running"]    = number_format($count_log > 0 ? $data[$count_log-1]->wallet_log_running_balance : 0,2);
        // $data->total_running    = number_format(Tbl_wallet_log::where("wallet_log_slot_id",$slot->slot_id)->where("currency_id",$currency_id)->first() ? Tbl_wallet_log::where("wallet_log_slot_id",$slot->slot_id)->where("currency_id",$currency_id)->orderBy("wallet_log_id","DESC")->first()->wallet_log_running_balance : 0,2); 
        
        // $data->wallet_balance   = $wallet_log !=null ? $wallet_log->wallet_log_running_balance : 0;
        // if($var['currency'] != 'all')
        // {
        //     $data->wallet_abb                   = Tbl_currency::where("currency_id", $var['currency'])->first();
        // }
        // else
        // {
        //     $data->wallet_abb                   = Tbl_currency::where("currency_id", $currency_id)->first();
        // }

        // rocky
        // $wallet["cash_out_history"]             = Tbl_wallet_log::cashout()->where("wallet_log_slot_id", $slot->slot_id)->get();
        

        // foreach($data as $key=>$value)
        // {
        //     if($var['currency'] != 'all')
        //     {
        //         $data[$key]["wallet_abb"]                   = Tbl_currency::where("currency_id", $var['currency'])->first();
        //     }
        //     else
        //     {
        //         $data[$key]["wallet_abb"]                   = Tbl_currency::where("currency_id", $currency_id)->first();
        //     }
        // }

        
        return $data;
    }


    public function cashin_history()
    {
        $slot_id                     = Request::input('slot_id');
        $slot                        = Tbl_slot::where('slot_id', $slot_id)->first();
        $cash_in_history             = Tbl_cash_in_proofs::where("cash_in_slot_code", $slot->slot_no)->orderBy('cash_in_proof_id', 'desc')->get();
        
        return response()->json($cash_in_history);
    }

    public function cashout_history()
    {
        $slot_id                     = Request::input('slot_id');
        $slot                        = Tbl_slot::where('slot_id', $slot_id)->first();
        $cashout_processing           = CashOut::get_transactions(null,$slot->slot_id);

        return response()->json($cashout_processing);

    }
    public function upgrade_history()
    {
        $slot_id = Request::input('slot_id');
        $upgrade_history = DB::table('tbl_membership_upgrade_logs')->where('slot_id', $slot_id)
                ->leftJoin('tbl_codes', 'tbl_codes.code_date_used', '=', 'tbl_membership_upgrade_logs.upgraded_at')
                ->orderBy('tbl_membership_upgrade_logs.upgraded_at', 'desc')
                ->get();

        foreach($upgrade_history as $index => $history) {
            $upgrade_history[$index]->old_membership_name = Tbl_membership::where('membership_id', $history->old_membership_id)->first()->membership_name;
            $upgrade_history[$index]->new_membership_name = Tbl_membership::where('membership_id', $history->new_membership_id)->first()->membership_name;
            $upgrade_history[$index]->slot_no = Tbl_slot::where('slot_id', $slot_id)->first()->slot_no;
        }

        return response()->json($upgrade_history);

    }
    public function current_slot()
    {   
        $slot = null;

        if(Request::input('slot_id')) 
        {
            $slot = Tbl_slot::owner()->where("slot_owner", Request::user()->id)->where("slot_id", Request::input('slot_id'))->where("slot_status","!=","blocked")->JoinMembership()->first();
            if(!$slot)
            {
                $slot = Tbl_slot::owner()->where("slot_owner", Request::user()->id)->where("slot_status","!=","blocked")->first();
            }

        }
        else
        {
            $slot = Tbl_slot::owner()->where("slot_owner", Request::user()->id)->where("slot_status","!=","blocked")->first();
        }

        Member::check_all_slot_id_number();
        
        $wallet = 0;
        if($slot)
        {
            $get_first = Tbl_slot::where("slot_owner", Request::user()->id)->where("slot_status","!=","blocked")->orderBy('slot_date_created')->first();
            $slot->check_processing = CashOut::get_processing_transaction($slot->slot_id);

            $wallet_info = Tbl_wallet::currency()->where('tbl_currency.currency_enable',1)->where("slot_id",$slot->slot_id)->get();
            if($wallet_info)
            {
                Wallet::generateSlotWalletAddress($slot->slot_id);

                $wallet = [];
                
                foreach ($wallet_info as $key => $value) 
                {
                    $wallet_info[$key]["wallet"] = Tbl_wallet::currency()->where("currency_abbreviation", $value->currency_abbreviation)->first();
                }
            }

            $slot->get_wallets              = $wallet_info;
            $slot->get_gc                   = Tbl_wallet::currency()->where("slot_id",$slot->slot_id)->where("tbl_currency.currency_abbreviation","GC")->first();
            
            $slot->slot_count               = Tbl_slot::where("slot_owner", Request::user()->id)->where("slot_status","!=","blocked")->count();
            $slot->slot_encrypted           = Crypt::encryptString($slot->slot_no);
            $slot->module_settings          = Module::get_module($slot->membership_inactive,$slot->slot_id);
            $slot->binary_settings          = Tbl_binary_settings::first();
            $slot->mlm_settings             = Tbl_mlm_settings::first();
            $slot->matrix_settigs          = Tbl_unilevel_matrix_bonus_settings::first();
            if($slot->slot_count_id >=  $slot->matrix_settigs->matrix_placement_start_at) {
                $slot->show_matrix = 1;
            } else {
                $slot->show_matrix = 0;
            }
            $slot->unilevel_settings        = Tbl_mlm_unilevel_settings::first();
            if($slot->unilevel_settings->unilevel_complan_show_to == 1) {
                if($slot->slot_count_id == 1) {
                    $slot->show_unilevel = 1;
                } else {
                    $slot->show_unilevel = 0;
                }
            } else {
                $slot->show_unilevel = 1;
            }
            $slot->cashout_settings         = Tbl_cash_out_settings::first();
            $slot_all                       = Tbl_slot::where("slot_owner", Request::user()->id)->where('slot_sponsor','!=',0)->first();
            $slot->replicated_sponsoring    = $slot->module_settings['replicated_member'];
            $slot->maintained_date_human    = Carbon::parse($slot->maintained_until_date)->format("M-d-Y h:m:s A");
            $slot->is_maintained            = Carbon::now() > Carbon::parse($slot->maintained_until_date) ? 0 : 1;
            $slot->first_slot               = $get_first;
            $slot->address_status           = Tbl_address::where('user_id',Request::user()->id)->where('archived',0)->where('is_default',1)->count();
            $slot->regCode                  = Tbl_address::where('user_id',Request::user()->id)->where('archived',0)->where('is_default',1)->pluck('regCode')->first();
            $slot->finders_pay              = Tbl_wallet_log::where('wallet_log_slot_id',$slot->slot_id)->where('wallet_log_details',"Finder's Pay")->sum('wallet_log_amount');
            $slot->accumulated_earnings     = Tbl_earning_log::where('earning_log_slot_id',$slot->slot_id)->sum('earning_log_amount') + $slot->finders_pay;
            $slot->direct_bonus             = Tbl_earning_log::where('earning_log_slot_id',$slot->slot_id)->where('earning_log_plan_type','DIRECT')->sum('earning_log_amount');
            $slot->indirect_bonus           = Tbl_earning_log::where('earning_log_slot_id',$slot->slot_id)->where('earning_log_plan_type','INDIRECT')->sum('earning_log_amount');
            $slot->binary_wallet            = Tbl_earning_log::where('earning_log_slot_id',$slot->slot_id)->where('earning_log_plan_type','BINARY')->sum('earning_log_amount');
            $slot->achievers_wallet         = Tbl_earning_log::where('earning_log_slot_id',$slot->slot_id)->where('earning_log_plan_type','ACHIEVERS RANK')->sum('earning_log_amount'); // Created By: Centy - 10-27-2023
            $slot->unilevel_wallet          = Tbl_earning_log::where('earning_log_slot_id',$slot->slot_id)->where('earning_log_plan_type',$slot->unilevel_settings->is_dynamic == 'normal' ? 'UNILEVEL' : 'UNILEVEL COMMISSION')->sum('earning_log_amount');
            $slot->rebates                  = Tbl_earning_log::where('earning_log_slot_id',$slot->slot_id)->where('earning_log_plan_type','PRODUCT PERSONAL CASHBACK')->sum('earning_log_amount');
            $slot->overriding_commission    = Tbl_earning_log::where('earning_log_slot_id',$slot->slot_id)->where('earning_log_plan_type','OVERRIDING COMMISSION V2')->sum('earning_log_amount');
            $slot->team_sales_bonus         = Tbl_earning_log::where('earning_log_slot_id',$slot->slot_id)->where('earning_log_plan_type','TEAM SALES BONUS')->sum('earning_log_amount');
            $slot->overriding_bonus         = Tbl_earning_log::where('earning_log_slot_id',$slot->slot_id)->where('earning_log_plan_type','OVERRIDING BONUS')->sum('earning_log_amount');
            $slot->store_name               = $slot->membership_inactive == 1 ? Tbl_slot::where('slot_id',$slot->slot_sponsor)->pluck('store_name')->first() : $slot->store_name;
            $slot->retailer_override        = Tbl_earning_log::where('earning_log_slot_id',$slot->slot_id)->where('earning_log_plan_type','RETAILER OVERRIDE')->sum('earning_log_amount');
            $slot->pass_up                  = Tbl_earning_log::where('earning_log_slot_id',$slot->slot_id)->where('earning_log_plan_type','PASS UP')->sum('earning_log_amount');
            $slot->dropshipping_bonus       = Tbl_earning_log::where('earning_log_slot_id', $slot->slot_id)->where('earning_log_plan_type', 'DROPSHIPPING BONUS')->sum('earning_log_amount');
            $slot->reverse_pass_up          = Tbl_earning_log::where('earning_log_slot_id',$slot->slot_id)->where('earning_log_plan_type','REVERSE PASS UP')->sum('earning_log_amount');
            $slot->binary_wallet            = Tbl_earning_log::where('earning_log_slot_id',$slot->slot_id)->where('earning_log_plan_type','BINARY')->where('earning_log_currency_id',1)->sum('earning_log_amount');
            $slot->gc_binary_wallet         = Tbl_earning_log::where('earning_log_slot_id',$slot->slot_id)->where('earning_log_plan_type','BINARY')->where('earning_log_currency_id',4)->sum('earning_log_amount');
            $slot->unilevel_matrix_wallet   = Tbl_earning_log::where('earning_log_slot_id',$slot->slot_id)->where('earning_log_plan_type','UNILEVEL MATRIX BONUS')->sum('earning_log_amount');
            $slot->cd_wallet                = Tbl_wallet::where("slot_id",$slot->slot_id)->where('currency_id',18)->pluck('wallet_amount')->first();
            $for_approval_status            = Tbl_orders_for_approval::where('user_id',Request::user()->id)->where('user_status',null)->where('date_purchased',null)->first() ?? null;
            $slot->for_approval_status      = isset($for_approval_status) ? 1 : 0;
            $slot->sponsor_name = Tbl_slot::where('slot_id',$slot->slot_sponsor)->first()->slot_no; 
            $slot->slot_limit               = Tbl_slot_limit::where('user_id',$slot->slot_owner)->first()->slot_limit;
            $slot->active_slots             = Tbl_slot_limit::where('user_id',$slot->slot_owner)->first()->active_slots;
            $slot->mentors_wallet           = Tbl_earning_log::where('earning_log_slot_id',$slot->slot_id)->where('earning_log_plan_type','MENTORS BONUS')->where('earning_log_currency_id',1)->sum('earning_log_amount');
            $slot->welcome_bonus_commission = Tbl_welcome_bonus_commissions::where('membership_id', $slot->slot_membership)->first()->commission ?? 0;
            $slot->welcome_bonus_wallet = Tbl_earning_log::where('earning_log_slot_id',$slot->slot_id)->where('earning_log_plan_type','WELCOME BONUS')->where('earning_log_currency_id',1)->sum('earning_log_amount');
            $slot->reward_points_wallet = Tbl_earning_log::where('earning_log_slot_id',$slot->slot_id)->where('earning_log_plan_type','REWARD POINTS')->where('earning_log_currency_id',1)->sum('earning_log_amount');
            $slot->prime_refund_points = Tbl_prime_refund_points_log::where('slot_id',$slot->slot_id)->where('status', 0)->sum('points');
            $slot->prime_refund_wallet = Tbl_earning_log::where('earning_log_slot_id',$slot->slot_id)->where('earning_log_plan_type','PRIME REFUND')->where('earning_log_currency_id',1)->sum('earning_log_amount');
            $slot->incentive_wallet = Tbl_earning_log::where('earning_log_slot_id',$slot->slot_id)->where('earning_log_plan_type','INCENTIVE')->where('earning_log_currency_id',1)->sum('earning_log_amount');
            $slot->milestone_bonus_settings = Tbl_milestone_bonus_settings::first();
            $slot->milestone_cycle_info =  Member::get_milestone_cycle_info($slot);
            $slot->milestone_wallet =  Tbl_earning_log::where('earning_log_slot_id',$slot->slot_id)->where('earning_log_plan_type','MILESTONE BONUS')->where('earning_log_currency_id',1)->sum('earning_log_amount');
            $slot->accumulated_left_milestone_points = Tbl_milestone_points_log::where('points_slot_id', $slot->slot_id)->where('points_receive_left', '>=', 0)->sum('points_receive_left');
            $slot->accumulated_right_milestone_points = Tbl_milestone_points_log::where('points_slot_id', $slot->slot_id)->where('points_receive_right', '>=', 0)->sum('points_receive_right');
            $slot->incentive_wallet = Tbl_earning_log::where('earning_log_slot_id',$slot->slot_id)->where('earning_log_plan_type','INCENTIVE')->where('earning_log_currency_id',1)->sum('earning_log_amount');
            $slot->infinity_bonus_wallet = Tbl_earning_log::where('earning_log_slot_id',$slot->slot_id)->where('earning_log_plan_type','INFINITY BONUS')->where('earning_log_currency_id',1)->sum('earning_log_amount');
            $marketingSupportEnable = Tbl_mlm_plan::where('mlm_plan_code','=','MARKETING_SUPPORT')->first() ? Tbl_mlm_plan::where('mlm_plan_code','=','MARKETING_SUPPORT')->first()->mlm_plan_enable : 0;
            if($marketingSupportEnable) {
                Member::update_daily_marketing_support_income($slot->slot_id);
                $slot->marketing_support_wallet = Tbl_earning_log::where('earning_log_slot_id', $slot->slot_id)->where('earning_log_plan_type','MARKETING SUPPORT')->where('earning_log_currency_id', 1)->sum('earning_log_amount');
                $slot->marketing_support_income = Tbl_marketing_support_log::where('log_slot_id',$slot->slot_id)->where('log_claimed', 1)->where('log_status', 0)->sum('log_income');
                $slot_count = Member::get_count_direct($slot);
                $slot->left_direct = $slot_count['left_direct'];
                $slot->right_direct = $slot_count['right_direct'];
                $slot->recurring_direct = $slot_count['recurring_direct'];
                $slot->marketing_support_number_of_daily_income = Tbl_marketing_support_log::where('log_slot_id',$slot->slot_id)->where('log_claimed', 1)->where('log_status', 0)->count();
                $settings = Tbl_marketing_support_settings::first();
                $slot->marketing_support_number_of_days_to_earn = $settings->number_of_days_to_earn;
                $slot->marketing_support_count_income = $slot->marketing_support_count_income;
                $slot->marketing_support_number_of_income = $settings->number_of_income;
                if($slot->marketing_support_count_income >= $settings->number_of_income) {
                    $slot->marketing_support_max_income = true;
                } else {
                    $slot->marketing_support_max_income = false;
                }
            }
            $leadersSupportEnable = Tbl_mlm_plan::where('mlm_plan_code','=','LEADERS_SUPPORT')->first() ? Tbl_mlm_plan::where('mlm_plan_code','=','MARKETING_SUPPORT')->first()->mlm_plan_enable : 0;
            if($leadersSupportEnable) {
                Member::update_leader_support_income($slot->slot_id);
                $slot->leaders_support_wallet = Tbl_earning_log::where('earning_log_slot_id', $slot->slot_id)->where('earning_log_plan_type','LEADERS SUPPORT')->where('earning_log_currency_id', 1)->sum('earning_log_amount');
                $slot->leaders_support_income = Tbl_leaders_support_log::where('log_slot_id',$slot->slot_id)->where('log_status', 0)->sum('log_income');
                $slot->leaders_support_settings = Tbl_leaders_support_settings::first();
            }
            $upgrade_logs = DB::table('tbl_membership_upgrade_logs')->where('slot_id', $slot->slot_id)->orderBy('membership_upgrade_log_id', 'desc')->first();
            if($upgrade_logs) {
                $upgrade_logs->old_membership_name = Tbl_membership::where('membership_id', $upgrade_logs->old_membership_id)->first()->membership_name;
                $upgrade_logs->new_membership_name = Tbl_membership::where('membership_id', $upgrade_logs->new_membership_id)->first()->membership_name;
                $slot->lastest_upgrade = $upgrade_logs;
            }
            if($slot_all)
            {
                $slot->slot_sponsor_code        = Tbl_slot::where('slot_id',$slot_all->slot_sponsor)->pluck('slot_no')->first();

            }
            else
            {
                $slot->slot_sponsor_code        =  "";
            }

            if($slot_all && $slot->replicated_sponsoring != 0)
            {
                $slot->slot_sponsored = Tbl_slot::where('slot_id',$slot_all->slot_sponsor_member)->value('slot_no');
            }
            else if($slot->slot_sponsor_member != 0 && $slot->membership_inactive == 1)
            {
                $slot->slot_sponsored = Tbl_slot::where('slot_id',$slot->slot_sponsor_member)->value('slot_no');
            }
            else
            {
                $slot->slot_sponsored = "";
            }
        }
        $reset = 0;

        if(!$reset) {
		    // $slots = Tbl_slot::where('slot_type','PS')->where('slot_id', '>', 1)->where('matrix_sponsor', 0)->where('matrix_position', null)->get();
            // foreach($slots as $s) {
            //     Mlm_complan_manager::unilevel_matrix_bonus($s);
            // }
        } else {
            $slots = Tbl_slot::where('slot_type','PS')->where('slot_id', '!=', 1)->get();

            foreach($slots as $s) {
                $update["slot_left_points"] = 0;
                $update["slot_right_points"] = 0;
                $update["slot_position"] = "";
                $update["slot_placement"] = 0;
                $update["slot_date_placed"] = null;
                $update["slot_pairs_per_day_date"] = "";
                $update["slot_pairs_per_day"] = 0;
                $update["meridiem"] = "";
                $update["slot_milestone_left_points"] = 0;
                $update["slot_milestone_right_points"] = 0;
                $update["slot_milestone_pairs"] = 0;
                $update["slot_milestone_pairs_date"] = null;
                $update["milestone_meridiem"] = null;
                $update["marketing_support_activate"] = 0;
                $update["marketing_support_count_income"] = 0;
                $update["marketing_support_date_start"] = null;
                $update["marketing_support_date_end"] = null;
                Tbl_slot::where('slot_id', $s->slot_id)->update($update);

                Tbl_wallet_log::where('wallet_log_slot_id', $s->slot_id)->delete();
                Tbl_earning_log::where('earning_log_slot_id', $s->slot_id)->delete();
                Tbl_wallet::where('slot_id', $s->slot_id)->where('currency_id', 1)->update(['wallet_amount' => 0]);
                Tbl_binary_points::where('binary_points_slot_id', $s->slot_id)->delete();
            
            }   
            \DB::table('tbl_tree_placement')->truncate();
            // \DB::table('tbl_unilevel_points')->truncate();
            \DB::table('tbl_points_log')->truncate();
            \DB::table('tbl_milestone_points_log')->truncate();
            \DB::table('tbl_binary_projected_income_log')->truncate();
        }
        if($slot && $slot->slot_membership) {

            $prime_refund = Tbl_mlm_plan::where('mlm_plan_code','=','PRIME_REFUND')->first() ? Tbl_mlm_plan::where('mlm_plan_code','=','PRIME_REFUND')->first()->mlm_plan_enable : 0;

            if($prime_refund && $slot->prime_refund_enable == 1) {
                $already_claimed = Tbl_prime_refund_points_log::where([
                    ["slot_id", $slot->slot_id],
                    ["status", 1] 
                ])->exists();
                $slot->prime_refund_status = $already_claimed;
            }
            Special_plan::check_livewell_rank($slot);
        }
        Member::check_all_slot_binary_projected_income();
        if($slot && $slot->binary_realtime_commission == 0) {
            $slot->binary_projected_income_wallet = Tbl_binary_projected_income_log::where('slot_id', $slot->slot_id)->where('status', 0)->sum('wallet_amount');
        }
        
        return json_encode($slot);
    }

    public function add_slot()
    {
        $error                 = 0;
        $data                  = Request::input('slot');
        $pass["pin"]           = $data['pin'];
        $pass["code"]          = $data['code'];
        $pass["slot_sponsor"]  = $data['slot_sponsor'];
        $pass["slot_owner"]    = Request::user()->id;
        $pass['slot_id']       = Request::input('slot_id');

        $register_your_slot    = Tbl_other_settings::where("key","register_your_slot")->first() ? Tbl_other_settings::where("key","register_your_slot")->first()->value : 1;
        $register_on_slot      = Tbl_other_settings::where("key","register_on_slot")->first() ? Tbl_other_settings::where("key","register_on_slot")->first()->value : 1;

        if($register_your_slot == 0 && $register_on_slot == 1)
        {
            $check_code = Code::get_membership_code_details($pass["code"],$pass["pin"]);
            if($check_code)
            {
                if($check_code->slot_qty == 1)
                {
                    $count_activated_slot = Tbl_slot::where("slot_owner",Request::user()->id)->where("membership_inactive",0)->count();
                    if($count_activated_slot != 0)
                    {
                        $response["status_message"][0] = "You can only use bundled kit for yourself...";
                        $response["status"]             = "error";
                        $response["status_code"]        = 400;

                        $error = 1;
                    }
                }
            }
        }

        if($error == 0)
        {
            $response              = Slot::create_slot($pass);
        }

        $response['position'] = Member::get_strong_leg_position(Request::input("slot_id"));

        return $response;
    }

    public function add_slot_with_register()
    {
        $new_user                      = Request::input('add_member');
        $new_user["register_platform"] = "system";

        $data                          = Request::input('slot');

        $pass["pin"]           = $data['pin'];
        $pass["code"]          = $data['code'];
        $pass["slot_sponsor"]  = $data['slot_sponsor'];
        $pass['slot_id']       = Request::input('slot_id');

        $return["i"]              = 0;
        $return["status_message"] = [];
        $return = Slot_create::validate_membership_code($return,$pass["code"],$pass["pin"]);  
        $return = Slot_create::validate_required($return,0,$pass["slot_sponsor"],2);    
        $return = Slot_create::validate_slot_no($return,null);  

        $check_code = Code::get_membership_code_details($pass["code"],$pass["pin"]);

        if($check_code)
        {
            if($check_code->code_user == 'buyer')
            {
                $return["status_message"][0]   = "Only the buyer can use this code.";
                $return["i"]                   = 1;
            }
        } 


        if($return["i"] == 0)
        {
            $register = Member::add_member($new_user,"register_area");
            if($register["status"] == "success")
            {
                $pass["slot_owner"]             = $register["status_data_id"];
                $response                       = Slot::create_slot($pass);
            }
            else
            {
                $response["status_message"] = $register["status_message"];
                $response["status"]         = "error";
            }
        }
        else
        {
            $response["status_message"] = $return["status_message"];
            $response["status"]         = "error";
        }

        return $response;
    }

    public function all_slot()
    {
        $slot = Tbl_slot::where("slot_owner", Request::user()->id)->where("slot_status","!=","blocked")->leftJoin('tbl_membership','tbl_membership.membership_id','tbl_slot.slot_membership')->get();
        $currency_default = Tbl_currency::where("currency_default",1)->first();
        $cycle                   = DB::table("tbl_binary_settings")->first() ? DB::table("tbl_binary_settings")->first()->cycle_per_day : 1;
        $enable_binary           = DB::table("tbl_mlm_plan")->where("mlm_plan_code","BINARY")->first() ? DB::table("tbl_mlm_plan")->where("mlm_plan_code","BINARY")->first()->mlm_plan_enable : 0;
        $total = 0;
        $binary_settings         = DB::table("tbl_binary_settings")->first();
        
        foreach ($slot as $key => $value) 
        {
            $sponsor    = Tbl_slot::where("slot_id", $value->slot_sponsor)->first();
            $wallet     = Tbl_wallet::where("slot_id", $value->slot_id)->where('currency_id',$currency_default->currency_id)->value('wallet_amount');
            $earning    = Tbl_earning_log::where("earning_log_slot_id", $value->slot_id)->where('earning_log_currency_id',$currency_default->currency_id)->sum('earning_log_amount');

            $total = $total+$earning;
            $slot[$key]->sponsor    = $sponsor;
            $slot[$key]->wallet     = number_format($wallet,2);
            $slot[$key]->earning    = number_format($earning,2);
            $slot[$key]->currency   = $currency_default->currency_abbreviation;
            
            
            $data['todays_pairs']  = Tbl_earning_log::where('earning_log_slot_id',$value->slot_id)->where('earning_log_plan_type','=','BINARY');
            if($cycle == 1)
            {
                $today = Carbon::now()->format('Y-m-d');
                $data['todays_pairs'] = $data['todays_pairs']->wheredate('earning_log_date_created',$today)->count();
            }
            else if($cycle == 2)
            {

                $currentDate = Carbon::now();
                $todayStart = $currentDate->copy()->startOfDay();
                $todayNoon = Carbon::createFromFormat('Y-m-d H:i:s', $currentDate->format('Y-m-d') . ' 12:00:00');
                $todayEnd = Carbon::createFromFormat('Y-m-d H:i:s', $currentDate->format('Y-m-d') . ' 23:59:59');
                // Count or sum pairs based on the time of day
                if ($currentDate->format('A') == "AM") {
                    // Morning logs: from the start of the day until noon
                    if ($binary_settings->binary_limit_type == 1) {
                        $data['todays_pairs'] = $data['todays_pairs']
                            ->where('earning_log_date_created', '>=', $todayStart)
                            ->where('earning_log_date_created', '<=', $todayNoon)
                            ->count();
                    } else if ($binary_settings->binary_limit_type == 2) {
                        $data['todays_pairs'] = $data['todays_pairs']
                            ->where('earning_log_date_created', '>=', $todayStart)
                            ->where('earning_log_date_created', '<=', $todayNoon)
                            ->sum('earning_log_amount');
                    }
                } else {
                    // Afternoon logs: from noon until the end of the day
                    if ($binary_settings->binary_limit_type == 1) {
                        $data['todays_pairs'] = $data['todays_pairs']
                            ->where('earning_log_date_created', '>', $todayNoon)
                            ->where('earning_log_date_created', '<=', $todayEnd)
                            ->count();
                    } else if ($binary_settings->binary_limit_type == 2) {
                        $data['todays_pairs'] = $data['todays_pairs']
                            ->where('earning_log_date_created', '>', $todayNoon)
                            ->where('earning_log_date_created', '<=', $todayEnd)
                            ->sum('earning_log_amount');
                    }
                }
            }
            else if($cycle == 3)
            {
                $start = Carbon::now()->startofWeek();
                $end = Carbon::now()->endofWeek();
                if($binary_settings->binary_limit_type == 1) {
                    $data['todays_pairs'] = $data['todays_pairs']->wheredate('earning_log_date_created',">=",$start)->wheredate('earning_log_date_created',"<=",$end)->count();
                } else if($binary_settings->binary_limit_type == 2) {
                    $data['todays_pairs'] = $data['todays_pairs']->wheredate('earning_log_date_created',">=", $start)->wheredate('earning_log_date_created',"<=",$end)->sum('earning_log_amount');
                }
            }
            else if($cycle == 4)
            {
                if($binary_settings->binary_limit_type == 1) {
                    $data['todays_pairs'] = $data['todays_pairs']->count();
                } else if($binary_settings->binary_limit_type == 2) {
                    $data['todays_pairs'] = $data['todays_pairs']->sum('earning_log_amount');
                }
            }
            
            $data['todays_pairs'] = round($data['todays_pairs'], 2);

            if($binary_settings->binary_limit_type == 1) {
                $data['max_pairs']      =  Tbl_slot::where('slot_id',$value->slot_id)->JoinMembership()->first() ? Tbl_slot::where('slot_id',$value->slot_id)->JoinMembership()->first()->membership_pairings_per_day :  0;
            } else if($binary_settings->binary_limit_type == 2) {
                $data['max_pairs']      =  Tbl_slot::where('slot_id',$value->slot_id)->JoinMembership()->first() ? Tbl_slot::where('slot_id',$value->slot_id)->JoinMembership()->first()->max_earnings_per_cycle : 0;
            }
            if($data['todays_pairs'] <= $data['max_pairs'] )
            {
                $data['remarks'] = 'ok';
            }
            else
            {
                $data['remarks'] = 'nah';
            }
            
            $slot[$key]->todays_pairs = $data['todays_pairs'];
            $slot[$key]->max_pairs    = $data['max_pairs'];
            $slot[$key]->remarks      = $data['remarks'];
            $slot[$key]->enable_binary= $enable_binary;

        }
        // $/
        if(count($slot) > 0) {
            $slot[0]['total_earned'] = $total;
        }
        return json_encode($slot);
    }

    public function count_slot()
    {
        $slot = Tbl_slot::where("slot_owner", Request::user()->id)->count();

        return json_encode($slot);
    }
    

    public function test()
    {
    	dd(Request::user());
    }

    public function upload_image(Request2 $request)
    {
        $file = $request->file('upload');
        $path_prefix = 'https://s3.us-west-000.backblazeb2.com/';
        $path = "mlm/".Request::input('folder');
        $storage_path = storage_path();

        if($file->isValid())
        {
            $full_path = Storage::disk('s3')->putFile($path, $file, "public");
            $url = Storage::disk('s3')->url($full_path);
            return json_encode($url);
        }
    }

    public function upload_video(Request2 $request)
    {
        $file = $request->file('upload');

        $path_prefix = 'https://s3.us-west-000.backblazeb2.com/';
        $path = "mlm/".Request::input('folder');
        $storage_path = storage_path();

        if($file->isValid())
        {
            $full_path = Storage::disk('s3')->putFile($path, $file, "public");
            $url = Storage::disk('s3')->url($full_path);
            return json_encode($url);
        }
    }


    public function get_service_charge()
    {
        $data = Tbl_service_charge::where("service_name", Request::input('service'))->first();
        return json_encode($data);
    }

    public function get_plan_settings()
    {
        $code    = Tbl_mlm_plan::get();
        foreach($code as $key => $value)
        {
            $plan[$value->mlm_plan_code]      = Tbl_mlm_plan::where('mlm_plan_code',$value->mlm_plan_code)->value('mlm_plan_enable');
        }
        return json_encode($plan);

    }

    public function user_search()
    {
        $search = Request::input('user_search');

        $return = Member::get('member', $search);
        
        return response()->json($return);
    }

    public function select_user()
    {
        $user_id = Request::input('user_id');

        $return = Tbl_slot::where('slot_owner', $user_id)->where('slot_count_id','<=',1)->get();
        // $return['membership'] = Tbl_slot::where('slot_owner', $user_id)->join('tbl_membership', 'tbl_slot.slot_membership', '=', 'tbl_membership.membership_id')->get();
        foreach($return as $key => $value)
        {
            $return[$key]['membership'] = Tbl_membership::where('membership_id', $value->slot_membership)->first();
        }
        return response()->json($return);

    }

    public function transfer_code()
    {
        $user_id       = Request::user()->id;
        $code_id       = Request::input('code_id');
        $transfer_from = Request::input('transfer_from');
        $transfer_to   = Request::input('transfer_to');
        $slot_to       = Tbl_slot::where('slot_id', $transfer_to)->first();

        $check_if_owned = Tbl_codes::where("code_id",$code_id)->where("code_sold_to",$user_id)->where("code_used",0)->first();
        if($check_if_owned)
        {        
            $check_if_first_log = DB::table('tbl_code_transfer_logs')->where('code_id', $code_id)->first();

            $update_code['code_sold_to'] = $slot_to->slot_owner;
            DB::table('tbl_codes')->where('code_id', $code_id)->update($update_code);

            $insert_log['code_id']         = $code_id;
            $insert_log['from_slot']       = $transfer_from;
            $insert_log['to_slot']         = $transfer_to;
            $insert_log['original_slot']   = $check_if_first_log ? $check_if_first_log->original_slot : $transfer_from;
            $insert_log['date_transfer']   = Carbon::now();

            $insert = DB::table('tbl_code_transfer_logs')->insertGetId($insert_log);
            
            if($slot_to->slot_membership) {
                $slot_to = Tbl_slot::JoinMembership()->where('slot_id', $transfer_to)->first();
                $code = Tbl_codes::Inventory()->InventoryItem()->where('code_id', $code_id)->first();
                if($slot_to->auto_activate_product_code && $code->item_type == "product") {
                    $data["pin"] = $code->code_pin;
                    $data["code"] = $code->code_activation;
                    $data["slot_id"] = $slot_to->slot_id;
                    $data["code_id"] = $code->code_id;
                    $data["slot_owner"] = $slot_to->slot_owner;
                    Product::activate_code($data);
                }
            }
            if(is_numeric($insert))
            {
                $return["status"]         = "success"; 
                $return["status_code"]    = 201; 
                $return["status_message"] = "Code Transferred";

                return response()->json($return);
            }
        }
        else
        {
            $return["status"]      = "error";
            $return["status_code"] = 400; 
            $return["status_message"]     = "This code cannot be transferred"; 

            return response()->json($return);      
        }
    
    }

    public function transfer_check_detail()
    {
        $email     = Request::input("transferred_to");
        $password  = Request::input("password"); 
        $owner_id  = Request::user()->id; 

        $check     = Users::where("email",$email)->select("id","name","email","contact","created_at")->where("type","member")->first();
        if($check)
        {
            $owner_info         = Users::where("id",$owner_id)->first();     
            if(Hash::check($password, $owner_info->password))
            {
                if($owner_info->id != $check->id)
                {
                    $check->date_joined = Carbon::parse($check->created_at)->format("F d, Y");
                }
                else
                {
                    $response["status"] = "error";
                    $response["message"] = "Invalid email";                    
                }
            }
            else
            {
                $response["status"] = "error";
                $response["message"] = "Incorrect password";
            }
        }
        else
        {
            $response["status"] = "error";
            $response["message"] = "Invalid email";
        }

        $response["data"] = $check;

        return response()->json($response);
    }

    public function check_if_maintain()
    {
        $id = Request::input("id");
        $return["unilevel"]  = Self::unilevel_status($id);
        $return["stairstep"] = Self::stairstep_status($id);
        $return["lockdown"]  = Tbl_other_settings::where("key","lockdown_enable")->first() ? Tbl_other_settings::where("key","lockdown_enable")->first()->value : 0; 
        return response()->json($return);
    }
    public function unilevel_status($id)
    {
        $slot                    = Tbl_slot::where("slot_owner",Request::user()->id)->where("slot_id",$id)->first();
        $data                    = null;
        $total_ppv               = 0;
        $total_gpv               = 0;
        $required_ppv            = 0;
        $log                     = null;
        $ctr                     = 0;

        if($slot)
        {
            $membership                            = Tbl_membership::where("membership_id",$slot->slot_membership)->first();
            if($membership)
            {
                $membership->membership_unilevel_level = $membership->membership_unilevel_level;
                $level                                 = 1;


                $required_ppv                    = $membership->membership_required_pv;
                $first_date                      = Carbon::now()->startOfMonth();
                $end_date                        = Carbon::now()->endOfMonth();

                $log[$ctr]["level_name"]         = "Personal Purchase";
                $log[$ctr]["number_of_slots"]    = Tbl_unilevel_points::where("unilevel_points_slot_id",$slot->slot_id)->where("unilevel_points_date_created",">=",$first_date)->where("unilevel_points_date_created","<=",$end_date)->where("unilevel_points_type","UNILEVEL_PPV")->count() ? Tbl_unilevel_points::where("unilevel_points_slot_id",$slot->slot_id)->where("unilevel_points_date_created",">=",$first_date)->where("unilevel_points_date_created","<=",$end_date)->where("unilevel_points_type","UNILEVEL_PPV")->count()." Purchase(s)" : "No Purchase";
                $log[$ctr]["last_slot_creation"] = Tbl_unilevel_points::where("unilevel_points_slot_id",$slot->slot_id)->where("unilevel_points_type","UNILEVEL_PPV")->first() ? Carbon::parse(Tbl_unilevel_points::where("unilevel_points_slot_id",$slot->slot_id)->where("unilevel_points_type","UNILEVEL_PPV")->orderBy("unilevel_points_date_created","DESC")->first()->unilevel_points_date_created)->format("m/d/Y") : "---";
                $log[$ctr]["earnings"]           = Tbl_unilevel_points::where("unilevel_points_slot_id",$slot->slot_id)->where("unilevel_points_type","UNILEVEL_PPV")->where("unilevel_points_date_created",">=",$first_date)->where("unilevel_points_date_created","<=",$end_date)->sum("unilevel_points_amount");

                $total_ppv = $total_ppv + $log[$ctr]["earnings"];

                $log[$ctr]["earnings"] = number_format($log[$ctr]["earnings"],2);

                $ctr++;

                while($membership->membership_unilevel_level >= $level)
                {
                    $log[$ctr]["level_name"]         = $this->ordinal($level);
                    $log[$ctr]["number_of_slots"]    = Tbl_unilevel_points::where("unilevel_points_slot_id",$slot->slot_id)->where("unilevel_points_date_created",">=",$first_date)->where("unilevel_points_date_created","<=",$end_date)->where("unilevel_points_cause_level",$level)->where("unilevel_points_type","UNILEVEL_GPV")->count() ? Tbl_unilevel_points::where("unilevel_points_slot_id",$slot->slot_id)->where("unilevel_points_cause_level",$level)->where("unilevel_points_date_created",">=",$first_date)->where("unilevel_points_date_created","<=",$end_date)->where("unilevel_points_type","UNILEVEL_GPV")->count()." Purchase(s)" : "No Purchase";
                    $log[$ctr]["last_slot_creation"] = Tbl_unilevel_points::where("unilevel_points_slot_id",$slot->slot_id)->where("unilevel_points_cause_level",$level)->where("unilevel_points_type","UNILEVEL_GPV")->first() ? Carbon::parse(Tbl_unilevel_points::where("unilevel_points_slot_id",$slot->slot_id)->where("unilevel_points_cause_level",$level)->where("unilevel_points_type","UNILEVEL_GPV")->orderBy("unilevel_points_date_created","DESC")->first()->unilevel_points_date_created)->format("m/d/Y") : "---";
                    $log[$ctr]["earnings"]           = Tbl_unilevel_points::where("unilevel_points_slot_id",$slot->slot_id)->where("unilevel_points_cause_level",$level)->where("unilevel_points_date_created",">=",$first_date)->where("unilevel_points_date_created","<=",$end_date)->where("unilevel_points_type","UNILEVEL_GPV")->sum("unilevel_points_amount");
                    
                    $total_gpv = $total_gpv + $log[$ctr]["earnings"]; 

                    $log[$ctr]["earnings"] = number_format($log[$ctr]["earnings"],2);

                    $ctr++;
                    $level++;
                }    
            }
        }

        $data["log"]           = $log; 
        $data["total_ppv"]     = number_format($total_ppv,2); 
        $data["total_gpv"]     = number_format($total_gpv,2); 
        $data["required_ppv"]  = number_format($required_ppv,2); 
        $data["passed"]        = $total_ppv >= $required_ppv ? 1 : 0; 
        $response = $data["passed"];
        return $response;
    }

    public function stairstep_status($id)
    {
        $slot                    = Tbl_slot::where("slot_owner",Request::user()->id)->where("slot_id",$id)->first();
        $data                    = null;
        $log                     = null;
        $required_ppv            = 0;
        $total_override_points   = 0;
        $total_all_personal_gpv  = 0;
        $total_all_personal_ppv  = 0;
        $rank_level              = 0;
        if($slot)
        {
            $first_date          = Carbon::now()->startOfMonth();
            $end_date            = Carbon::now()->endOfMonth();

            $log = Tbl_stairstep_points::where("stairstep_points_slot_id",$slot->slot_id)
                                    ->leftJoin("tbl_slot","tbl_slot.slot_id","=","tbl_stairstep_points.stairstep_points_cause_id")
                                    ->leftJoin("users","users.id","=","tbl_slot.slot_owner")
                                    ->where("stairstep_points_type","STAIRSTEP_GPV")
                                    ->where("stairstep_points_date_created",">=",$first_date)
                                    ->where("stairstep_points_date_created","<=",$end_date)
                                    ->select("*",DB::raw("DATE_FORMAT(tbl_stairstep_points.stairstep_points_date_created, '%m/%d/%Y') as stairstep_points_date_created"))
                                    ->get();
   
            $total_override_points     = Tbl_stairstep_points::where("stairstep_points_slot_id",$slot->slot_id)->where("stairstep_points_type","STAIRSTEP_GPV")->where("stairstep_points_date_created",">=",$first_date)->where("stairstep_points_date_created","<=",$end_date)->sum("stairstep_override_points");
            $total_personal_pv         = Tbl_stairstep_points::where("stairstep_points_slot_id",$slot->slot_id)->where("stairstep_points_type","STAIRSTEP_PPV")->where("stairstep_points_date_created",">=",$first_date)->where("stairstep_points_date_created","<=",$end_date)->sum("stairstep_points_amount");
            
            $total_all_personal_gpv    = Tbl_stairstep_points::where("stairstep_points_slot_id",$slot->slot_id)->sum("stairstep_points_amount");
            $total_all_personal_ppv    = Tbl_stairstep_points::where("stairstep_points_slot_id",$slot->slot_id)->sum("stairstep_points_amount");

            $get_rank = Tbl_stairstep_rank::where("stairstep_rank_id",$slot->slot_stairstep_rank)->first();
            if($get_rank)
            {
                $required_ppv = $get_rank->stairstep_rank_personal;
                $rank_level   = $get_rank->stairstep_rank_level;
            }
        }

        $data["log"]                     = $log;  
        $data["total_override_points"]   = number_format($total_override_points,2); 
        $data["total_personal_pv"]       = number_format($total_personal_pv,2); 
        $data["required_ppv"]            = number_format($required_ppv,2); 

        $data["total_all_personal_gpv"]  = number_format($total_all_personal_gpv); 
        $data["total_all_personal_ppv"]  = number_format($total_all_personal_ppv); 
        $data["passed"]                  = $total_personal_pv >= $required_ppv ? 1 : 0;

        $all_rank = Tbl_stairstep_rank::where("archive",0)->get();

        foreach($all_rank as $key => $rnk)
        {
            $all_rank[$key]->all_ppv_percentage = ($total_all_personal_ppv >= $rnk->stairstep_rank_personal_all) ? "Qualified (100%)" : $total_all_personal_ppv." of ".$rnk->stairstep_rank_personal_all." (".(($total_all_personal_ppv/$rnk->stairstep_rank_personal_all) * 100)."%)"; 
            $all_rank[$key]->all_gpv_percentage = ($total_all_personal_gpv >= $rnk->stairstep_rank_group_all) ? "Qualified (100%)" : $total_all_personal_gpv." of ".$rnk->stairstep_rank_group_all." (".(($total_all_personal_gpv/$rnk->stairstep_rank_group_all) * 100)."%)";
            $all_rank[$key]->qualified          = $rank_level > $rnk->stairstep_rank_level ? 1 : 0;

            if($all_rank[$key]->qualified == 0)
            {
                if($all_rank[$key]->all_ppv_percentage == "Qualified (100%)" && $all_rank[$key]->all_gpv_percentage == "Qualified (100%)")
                {
                    $all_rank[$key]->qualified = 1;
                }
            }
        }   

        $data["all_rank"]     = $all_rank;
        $data["current_rank"] = isset($get_rank) ? $get_rank->stairstep_rank_name : 0;
        $response = $data["passed"];
        return $response;

    }
    public function check_item_unilevel()
    {
        $return["items"]   = DB::table("tbl_unilevel_items")->leftJoin("tbl_item","tbl_item.item_id","=","tbl_unilevel_items.item_id")
                                                   ->where("included",1)
                                                   ->select('included','item_sku','item_qty','tbl_unilevel_items.item_id','tbl_unilevel_items_id')->get();
        $return["enable"] =  DB::table("tbl_mlm_unilevel_settings")->first()->auto_ship;
        $return["active"] = Tbl_mlm_plan::where("mlm_plan_code","UNILEVEL")->first() ? Tbl_mlm_plan::where("mlm_plan_code","UNILEVEL")->first()->mlm_plan_enable : 0;
        return response()->json($return);
    }
    public function check_item_stairstep()
    {
        $return["items"]   = DB::table("tbl_stairstep_items")->leftJoin("tbl_item","tbl_item.item_id","=","tbl_stairstep_items.item_id")
                                                    ->where("included",1)
                                                    ->select('included','item_sku','item_qty','tbl_stairstep_items.item_id','tbl_stairstep_items_id')->get();
        $return["enable"] =  DB::table("tbl_stairstep_settings")->first()->auto_ship;
        $return["active"] = Tbl_mlm_plan::where("mlm_plan_code","STAIRSTEP")->first() ? Tbl_mlm_plan::where("mlm_plan_code","STAIRSTEP")->first()->mlm_plan_enable : 0;
        return response()->json($return);
    }
    public function check_item_lockdown()
    {
        $date_before       = Tbl_other_settings::where("key","lockdown_grace_period")->first() ? Tbl_other_settings::where("key","lockdown_grace_period")->first()->value : 0;
        $date_today        = Carbon::now();
        $maintained_date   = Tbl_slot::where("slot_id",Request::input("slot_id"))->select("maintained_until_date")->first()->maintained_until_date;

        if($date_before != 0 || $maintained_date != null)
        {
            $date_maintained_add_before = Carbon::parse($maintained_date)->subDays($date_before);
            // dd($date_today,$date_maintained_add_before,($date_today>=$date_maintained_add_before));
            if($date_today >= $date_maintained_add_before)
            {
                $return["items"]   = Tbl_lockdown_autoship_items::leftJoin("tbl_item","tbl_item.item_id","=","tbl_lockdown_autoship_items.item_id")
                                                                    ->where("included",1)     
                                                                    ->select('included','item_sku','item_qty','lockdown_autoship_items_id',"tbl_lockdown_autoship_items.item_id")->get();
            }
            else 
            {
                $return["status"] = "nope";
            }
        }
        else 
        {
            $return["status"] = "nope";
        }


        
        return response()->json($return);
    }
    function ordinal($number) 
    {
        $ends = array('th','st','nd','rd','th','th','th','th','th','th');
        if ((($number % 100) >= 11) && (($number%100) <= 13))
            return $number. 'th';
        else
            return $number. $ends[$number % 10];
    }
    public function get_showing_settings()
    {
        $response['show_slot_code'] = Tbl_other_settings::where('key','show_slot_code')->first() ? Tbl_other_settings::where('key','show_slot_code')->first()->value : 1;
        $response['show_product_code'] = Tbl_other_settings::where('key','show_product_code')->first() ? Tbl_other_settings::where('key','show_product_code')->first()->value : 1;
        return $response;
    }

    public function cashout_receipt_data()
    {
        $cashout_id = Request::input('cashout_id');

        $return['cashout_details'] = Tbl_cash_out_list::where('cash_out_id', $cashout_id)->Method()->Slot()->first();
        $slot               = Tbl_cash_out_list::where('cash_out_id', $cashout_id)->Slot()->first();
        $return['direct']    = Tbl_tree_sponsor::where('sponsor_parent_id',$slot->slot_id)->where('sponsor_level', 1)->count();
        $return['indirect']    = Tbl_tree_sponsor::where('sponsor_parent_id',$slot->slot_id)->where('sponsor_level', '>', 1)->count();

        return response()->json($return);
    }

    public function get_company_details()
    {
        $return = DB::table('tbl_company_details')->first();

        return response()->json($return);
    }
    public function upgrade_kit()
    {
        $i = 0;
        $data = Request::input();
        $id   = Request::user()->id;
        $rules["code"]  = "required";
		$rules["pin"]       = "required";
        $rules["slot_to_upgrade"]  = "required|exists:tbl_slot,slot_no";
		$validator = Validator::make($data, $rules);
        if ($validator->fails())
        {
            
			foreach ($validator->errors()->getMessages() as $key => $value)
			{
				foreach($value as $val)
				{
					$return["status_message"][$i] = $val;
				    $i++;
				}
			}
        }
        else
        {
            $check_code = Code::check_membership_code_unused($data["code"],$data["pin"]);
            if($check_code == "unused")
            {
                $owner_info         = Users::where("id",$id)->first();
                if($owner_info->registered_as_retailer == 0)
                {
                    $target_info = Tbl_slot::where("slot_no",$data['slot_to_upgrade'])->where("membership_inactive",0)->Owner()->JoinMembership()->first();
                    if($target_info)
                    {
                        if($target_info->registered_as_retailer == 0)
                        {
                            $code_list = Tbl_codes::where('code_sold_to', $id)->where("code_activation",$data["code"])->where("code_pin",$data["pin"])->Inventory()->InventoryItem()->InventoryItemMembership()->where('tbl_item.item_type', 'membership_kit')->CheckIfArchived()->first();
                            if($code_list["slot_qty"] <= 1)
                            {
                                if($code_list["is_kit_upgrade"] != 0)
                                {
                                    if($target_info["hierarchy"] < $code_list["hierarchy"])
                                    {
                                        //-----------------logs------------------------
                                        $old_membership_binary_realtime_commission = $target_info["binary_realtime_commission"];
                                        $log['slot_id'] = $target_info["slot_id"];
                                        $log['old_membership_id'] = $target_info["slot_membership"];
                                        $log['new_membership_id'] = $code_list["membership_id"];
                                        $log['upgraded_at']       = Carbon::now();
                                        DB::table('tbl_membership_upgrade_logs')->insert($log);
                                        //----------------using_code--------------------
                                        $update["code_used"] = 1;
                                        $update["code_sold"] = 1;
                                        $update["code_slot_used"] = $target_info["id"];
                                        $update["code_used_by"] = $id;
                                        $update["code_date_used"] = Carbon::now();
                                        Tbl_codes::where("code_id",$code_list["code_id"])->update($update);
                                        //----------------updateing_membership---------------------------
                                        $update_membership["slot_membership"]  = $code_list["membership_id"];
                                        Tbl_slot::where("slot_id",$target_info["slot_id"])->update($update_membership);
                                        //------------------trigger_complan_direct-------------
                                        $target_info = Tbl_slot::where("slot_no",$data['slot_to_upgrade'])->where("membership_inactive",0)->Owner()->JoinMembership()->first();
                                        // Mlm_complan_manager::DIRECT($target_info);
                                        $check_plan_enable = Tbl_mlm_plan::where('mlm_plan_code','=','BINARY')->first() ? Tbl_mlm_plan::where('mlm_plan_code','=','BINARY')->first()->mlm_plan_enable : 0;
                                        if($check_plan_enable == 1) {
                                            Mlm_complan_manager::BINARY($target_info);
                                        }
                                    
                                        MLM::create_entry($target_info['slot_id']);
                                        if($old_membership_binary_realtime_commission == 0 && $code_list->binary_realtime_commission == 1) {
                                            Member::log_binary_projected_income($target_info);
                                        }
                                        $return["status"]             = "success";
                                        $return["status_message"] = "Membership Upgraded";
                                        //------------------trigger_flush_out-------------
                                        
                                        if($target_info["flushout_enable"] == 1)
                                        {
                                            $points['slot_right_points'] = 0;
                                            $points['slot_left_points'] = 0;
                                            $update_points['slot_right_points'] = $target_info["slot_right_points"];
                                            $update_points['slot_left_points'] = $target_info["slot_left_points"];
                                            $receive["left"] = (-1 * $update_points['slot_left_points']);
                                            $receive["right"] = (-1 * $update_points['slot_right_points']);
                                            $old["left"] = $target_info["slot_left_points"];
                                            $old["right"] = $target_info["slot_right_points"];
                                            $new["left"] = 0;
                                            $new["right"] = 0;
                                            $flushout_points["left"] = $target_info["slot_left_points"];
                                            $flushout_points["right"] = $target_info["slot_right_points"];
                                            $plan_type_left = "BINARY_LEFT_FLUSHOUT";
                                            Log::insert_points($target_info["slot_id"],(-1 * $update_points['slot_left_points']),$plan_type_left,$data["slot_id"], 0);                                 
                                            $plan_type_right = "BINARY_RIGHT_FLUSHOUT";
                                            Log::insert_points($target_info["slot_id"],(-1 * $update_points['slot_right_points']),$plan_type_right,$data["slot_id"], 0);   
                                            Tbl_slot::where('slot_id', $target_info["slot_id"])->update($points);
                                            Log::insert_binary_points($target_info["slot_id"],$receive,$old,$new,$data["slot_id"],0,0,0,"Membership Upgrade",0,$flushout_points,0);
                                        }

                                    }
                                    else if ($target_info["hierarchy"] == $code_list["hierarchy"]) 
                                    {
                                        $return["status_message"][$i] = "You are using same membership.";
                                        $i++; 
                                    }
                                    else 
                                    {
                                        $return["status_message"][$i] = "Use kit with higher membership.";
                                        $i++;  
                                    }
                                    
                                }
                                else 
                                {
                                    $return["status_message"][$i] = "This code is not for membership upgrade.";
                                    $i++; 
                                }
                            }
                            else 
                            {
                                $return["status_message"][$i] = "Cannot use bundle in upgrading membership.";
                                $i++; 
                            }
                        }
                        else 
                        {
                            $return["status_message"][$i] = "Slot to upgrade is retailers, cannot use this module.";
				            $i++;
                        }
                    }
                    else 
                    {
                        $return["status_message"][$i] = "Invalid slot to upgrade.";
				        $i++;
                    }
                }
                else 
                {
                    $return["status_message"][$i] = "Retailers cannot use this module.";
				    $i++;
                }
            }
            else if($check_code == "used")
            {
                $return["status_message"][$i] = "The code is already used.";
				$i++;
            }
            else 
            {
                $return["status_message"][$i] = "This code does not exist.";
				$i++;
            }

        }
        return response()->json($return);
    }
    public function get_total()
    {
        $currency_default = Tbl_currency::where("currency_default",1)->first();
        $slot = Tbl_slot::where("slot_owner", Request::user()->id)->where("slot_status","!=","blocked")->pluck("slot_id");
        $ok = [];
        $plan_enable = [];
        $plan_enable = Tbl_mlm_plan::where('mlm_plan_enable', 1 )
                        ->where('mlm_plan_code', '!=', 'LIVEWELL_RANK')
                        ->get();
        $total_running_balance = 0; 
        $currency_id = Tbl_currency::where('currency_default',1)->first()->currency_id;
        $mentors_bonus_enable = Tbl_membership::where('mentors_level', '!=', 0)->exists() ? 1 : 0;

        $new_plan = null;
        foreach($plan_enable as $key => $plan_name) {
            if($plan_name->mlm_plan_code == 'BINARY' && $mentors_bonus_enable) {
                $new_plan = new stdClass();
                $new_plan->mlm_plan_id = 100;
                $new_plan->mlm_plan_code = "MENTORS_BONUS";
                $new_plan->mlm_plan_label = "";
                $new_plan->mlm_plan_type = "";
                $new_plan->mlm_plan_trigger = "Binary Genealogy";
                $new_plan->mlm_plan_enable = 1;
            }
        }

        if ($new_plan !== null) {
            $plan_enable->push($new_plan);
        }
        
        foreach($plan_enable as $key => $plan_name)
        {
            $new_plan_name = trim(preg_replace('/_/', ' ', $plan_name->mlm_plan_code));
            $total = Tbl_earning_log::whereIn("earning_log_slot_id",$slot)
                                        ->where('earning_log_currency_id',$currency_id)
                                        ->where('earning_log_plan_type', $new_plan_name)
                                        ->sum("earning_log_amount");
                $new_number = number_format($total,2); 
                $plan_name = Tbl_label::where('plan_code',$plan_name->mlm_plan_code)->pluck('plan_name')->first();
                $obj = (object) array('plan_name' => $plan_name, 'amount' => $currency_default->currency_abbreviation .' '.$new_number);
                array_push($ok, $obj);
        }

        $total_running_balance = Tbl_earning_log::whereIn("earning_log_slot_id",$slot)
                                ->where('earning_log_currency_id',$currency_id)
                                ->sum("earning_log_amount");

        $return['total'] = $ok;
        $return['total_running_balance'] = $total_running_balance;
        return response()->json($return);
    }

    public function slot_preview()
    {
        $data                          = Request::input('slot');

        $pass["pin"]           = $data['pin'];
        $pass["code"]          = $data['code'];
        $pass["slot_sponsor"]  = $data['slot_sponsor'];
        $pass['slot_id']       = Request::input('slot_id');

        $return["i"]              = 0;
        $return["status_message"] = [];
        $return = Slot_create::validate_membership_code($return,$pass["code"],$pass["pin"]);  
        $return = Slot_create::validate_required($return,0,$pass["slot_sponsor"],2);    
        $return = Slot_create::validate_slot_no($return,null);  

        $check_code = Code::get_membership_code_details($pass["code"],$pass["pin"]);

        if($check_code)
        {
            if($check_code->code_user == 'buyer')
            {
                $return["status_message"][0]   = "Only the buyer can use this code.";
                $return["i"]                   = 1;
            }
        } 


        if($return["i"] == 0)
        {
            $response["details"]        = Tbl_slot::where("slot_no",$pass["slot_sponsor"])->Owner()->first();
        }
        else
        {
            $response["status_message"] = $return["status_message"];
            $response["status"]         = "error";
        }
        
        return response()->json($response);
    }

    public function slot_preview_place_own_downline()
    {
        $type = Request::input("type"); 
        $_data = Request::input("data");
        $owner_id = $_data["owner_id"];
        $data["slot_code"] = $_data["slot_no"];
        $data["slot_position"] =  $_data["position"];
        $binary_settings = Tbl_binary_settings::first();
        $position = Member::get_strong_leg_position($owner_id);
        if($binary_settings->binary_auto_placement_based_on_direct && $binary_settings->binary_number_of_direct_for_auto_placement > 0 && $position) {
            $data['slot_position'] = $position;
            $slot = Tbl_slot::where('slot_id',$owner_id)->first();
            $last_outer =Tbl_tree_placement::where("placement_parent_id",$owner_id)->where("placement_position",$data['slot_position'])->where("position_type","OUTER")->orderBy('tree_placement_id','desc')->first();
            if($last_outer)
            {
                $slot2 = Tbl_slot::where('slot_id',$last_outer->placement_child_id)->first();
                $data['slot_placement'] = $slot2->slot_no;
            } else {
                $data['slot_placement'] = $slot->slot_no;
            }
        } else if ($binary_settings->binary_extreme_position) {
            $slot = Tbl_slot::where('slot_id',$owner_id)->first();
            $last_outer =Tbl_tree_placement::where("placement_parent_id",$owner_id)->where("placement_position",$data['slot_position'])->where("position_type","OUTER")->orderBy('tree_placement_id','desc')->first();
            if($last_outer)
            {
                $slot2 = Tbl_slot::where('slot_id',$last_outer->placement_child_id)->first();
                $data['slot_placement'] = $slot2->slot_no;
            } else {
                $data['slot_placement'] = $slot->slot_no;
            }
        } else {
            $data["slot_placement"] = $_data["placement"];
        }

        $i = 0;
		$return["status_message"] = [];

		$placement = $data["slot_placement"];
		$position  = $data["slot_position"];
		$slot_no   = $data["slot_code"];
		$rules["slot_placement"]  = "required|exists:tbl_slot,slot_no";
		$rules["slot_code"]       = "required|exists:tbl_slot,slot_no";

		$validator = Validator::make($data, $rules);
        if ($validator->fails())
        {
			foreach ($validator->errors()->getMessages() as $key => $value)
			{
				foreach($value as $val)
				{
					$response["status_message"][$i] = $val;
				    $i++;
				}
			}
        }
        else
        {
         	if($position != "LEFT" && $position != "RIGHT")
         	{
				$response["status_message"][$i] = "Please select Placement Position";
				$i++;
         	}

         	$target_slot     	 	= Tbl_slot::where("slot_no",$slot_no)->first();


			/* PREVENTS MULTIPLE PROCESS AT ONE TIME */
			$user_process_level = 1;
			Tbl_user_process::where("user_id",$target_slot->slot_owner)->delete();

			$insert_user_process["level_process"] = $user_process_level;
			$insert_user_process["user_id"]       = $target_slot->slot_owner;
			Tbl_user_process::where("user_id",$target_slot->slot_owner)->where("level_process",$user_process_level)->insert($insert_user_process);

			while($user_process_level <= 4)
			{
				$user_process_level++;
				$insert_user_process["level_process"] = $user_process_level;
				$insert_user_process["user_id"]       = $target_slot->slot_owner;
				Tbl_user_process::where("user_id",$target_slot->slot_owner)->where("level_process",$user_process_level)->insert($insert_user_process);

				$count_process_before = Tbl_user_process::where("user_id",$target_slot->slot_owner)->where("level_process", ($user_process_level - 1) )->count();

				if($count_process_before != 1)
				{
				   $response["status_message"][$i] = "Please try again...";
				   $i++;
			  	   break;
				}
			}


			Tbl_user_process::where("user_id",$target_slot->slot_owner)->delete();

         	$slot_id         	 	= $target_slot->slot_id;
         	$slot_sponsor_id 	 	= $target_slot->slot_sponsor;


         	$placement       	 	= Tbl_slot::where("slot_no",$placement)->first()->slot_id;
         	$check_placement 	 	= Tbl_slot::where("slot_placement",$placement)->where("slot_position",$position)->first();
        
            $check_binary_settings  = Tbl_binary_settings::first();
			$check_plan_binary      = Tbl_mlm_plan::where('mlm_plan_code','=','BINARY')->first()->mlm_plan_enable;
			if($check_binary_settings)
			{
				if($check_binary_settings->crossline == 1 && $check_plan_binary == 1)
				{
					if($slot_sponsor_id != $placement)
					{
						$check_sponsor_under = Tbl_tree_placement::where('placement_parent_id',$slot_sponsor_id)->where('placement_child_id',$placement)->first();
						if($check_sponsor_under == null)
						{
							$response["status_message"][$i] = "Attempting crossline...";
							$i++;
						}
					}
				}
			}
         	if($check_placement)
         	{
				$response["status_message"][$i] = "Placement already taken...";
				$i++;
         	}
         	else
         	{
         		$check_placement = Tbl_slot::where("slot_id",$placement)->first();
				if( ($check_placement->slot_placement == 0 && $check_placement->slot_sponsor != 0) || $check_placement->membership_inactive == 1)
				{
					$response["status_message"][$i] = "Placement is not allowed on unplaced slot";
					$i++;
				}
         	}

         	if($target_slot->slot_placement != 0)
         	{
				$response["status_message"][$i] = "This slot is already placed.";
				$i++;
         	}


         	// if($type == "member_owned")
         	// {
         	// 	$slot_owned  = Tbl_slot::where("slot_no",$slot_no)->where("slot_owner",$owner_id)->first();
         	// 	if(!$slot_owned)
         	// 	{
			// 		$response["status_message"][$i] = "Error 501...";
			// 		$i++;
         	// 	}

         	// }

         	if($type == "member_downline")
         	{
                 $slot_owned  = Tbl_slot::where("slot_no",$slot_no)->first();
         		if(!$slot_owned)
         		{
					$response["status_message"][$i] = "Error 501...";
					$i++;
         		}
         		else
         		{
                    $check_sponsor = Tbl_slot::where("slot_id",$slot_owned->slot_sponsor)->where("slot_owner",$owner_id)->first();
         			if(!$check_sponsor)
         			{
						$response["status_message"][$i] = "Error 503...";
						$i++;
         			}
         		}
         	}
        }
        if($i == 0)
        {
            $response["details"]        = Tbl_slot::where("slot_no",$data["slot_placement"])->Owner()->first();
        }
        else
        {
            $response["status"]         = "error";
			$response["status_code"]    = 400;
        }
        return response()->json($response);
    }

    public function bulk_slot_preview()
    {
        $data                  = Request::input('slot');

        $pass["slot_sponsor"]  = $data['slot_sponsor'];

        $return["i"]              = 0;
        $return["status_message"] = [];
        $return = Slot_create::validate_required($return,0,$pass["slot_sponsor"],2);    
        $return = Slot_create::validate_slot_no($return,null);  
        if($return["i"] == 0)
        {
            $response["details"]        = Tbl_slot::where("slot_no",$pass["slot_sponsor"])->Owner()->first();
        }
        else
        {
            $response["status_message"] = $return["status_message"];
            $response["status"]         = "error";
        }
        
        return response()->json($response);
    }
    public function bulk_trans_slot_preview()
    {
        $id                  = Request::input('slot');
        $data['details']  = Tbl_slot::where("slot_id",$id)->first() ? Tbl_slot::where("slot_id",$id)->Owner()->first() : null;
        if(!$data['details'])
        {
            $return["i"]              = 0;
            $return["status_message"] = [];
            $return["status_message"][$return["i"]] = "Error Slot code.";
            $response["status_message"] =  $return["status_message"];
            $response["status"]         = "error";
        }
        else 
        {
            $response["details"] =$data['details'];
        }
        
        return response()->json($response);
    }
    public function check_user_info()
    {
        $user_id                            = Request::user()->id;

        $check_if_verify                    = Users::where('id',$user_id)->pluck('email_verified')->first();

        return $check_if_verify;
    }
    public function move_wallet()
    {
        $slot_id                                        = Request::input('slot_id');
        $main_account                                   = Request::input('main_account');
        $wallet_type                                    = Request::input('wallet_type');
        $amount                                         = Request::input('amount');
        $minimum_move_wallet                            = Request::input('minimum_move_wallet');
        $slot_no                                        = Tbl_slot::where('slot_id',$slot_id)->pluck('slot_no')->first();
        $convert_wallet                                 = 0;
        $get_wallet                                     = Tbl_wallet::where('slot_id',$slot_id)->where('currency_id',$wallet_type)->pluck('wallet_amount')->first() ?? 0;
        $wallet_deduction                               = $amount;
        $receivable_amount                              = $amount - Request::input('move_wallet_fee');

        
        if($receivable_amount > 0)
        {
            if($get_wallet >= $wallet_deduction)
            {
                if($amount < $minimum_move_wallet)
                {
                    $return['status_code']              = 400;    
                    $return['status_message']           = 'Minimum amount to move is PHP '.$minimum_move_wallet;
                } 
                else
                {
                    $convert_wallet = 1;
                }           
            }
            else
            {
                $return['status_code']                  = 400;    
                $return['status_message']               = 'Insufficient Wallet Balance';
            }
    
            if($convert_wallet == 1)
            {
                $return['status_code']                  = 200;    
                $return['status_message']               = 'Wallet Move Successfully';

                Log::insert_wallet($slot_id,$wallet_deduction * -1,"Move Wallet",$wallet_type);
                Log::insert_wallet($main_account,$receivable_amount,"Move Wallet from (".$slot_no.")", 1);
            }
        }
        else
        {
            $return['status_code']                      = 400;    
            $return['status_message']                   = 'Invalid amount to move';
        }

        return $return;
    }
    public function get_plan_label()
    {
        $plan    = [];
        $code    = Tbl_label::get();
        foreach($code as $key => $value)
        {
            $plan[$value->plan_code]      = Tbl_label::where('plan_code',$value->plan_code)->value('plan_name');
        }
        return json_encode($plan);
    }
    public function add_downline()
    {

        $code_id  = Request::input('code_id');

        if($code_id > 0)
        {
            $response = Member::add_member(request()->all());

            if($response['status'] == 'success')
            {
                $get_code              = Tbl_codes::where('code_id',$code_id)->first();
                $slot_info             = Tbl_slot::where('slot_no', Request::input('username'))->first();
                $error                 = 0;
                $pass["pin"]           = $get_code->code_pin;
                $pass["code"]          = $get_code->code_activation;
                $pass["slot_sponsor"]  = Request::input('slot_referral');
                $pass["slot_owner"]    = $slot_info->slot_owner;
                $pass['slot_id']       = $slot_info->slot_id;

                $register_your_slot    = Tbl_other_settings::where("key","register_your_slot")->first() ? Tbl_other_settings::where("key","register_your_slot")->first()->value : 1;
                $register_on_slot      = Tbl_other_settings::where("key","register_on_slot")->first() ? Tbl_other_settings::where("key","register_on_slot")->first()->value : 1;

                if($register_your_slot == 0 && $register_on_slot == 1)
                {
                    $check_code = Code::get_membership_code_details($pass["code"],$pass["pin"]);
                    if($check_code)
                    {
                        if($check_code->slot_qty == 1)
                        {
                            $count_activated_slot = Tbl_slot::where("slot_owner",$slot_info->slot_owner)->where("membership_inactive",0)->count();
                            if($count_activated_slot != 0)
                            {
                                $response["status_message"][0] = "You can only use bundled kit for yourself...";
                                $response["status"]             = "error";
                                $response["status_code"]        = 400;

                                $error = 1;
                            }
                        }
                    }
                }

                if($error == 0)
                {
                    $response              = Slot::create_slot($pass);
                }
            }

        }  
        else
        {
            $response["status"]                 = "error";
			$response["status_code"]            = 400;
			$response["status_message"][0]      = "Invalid Membership Package!";
        }
        $response['position'] = Member::get_strong_leg_position(Request::input("slot_id"));
        $response['placement_enable'] = Tbl_slot::where('slot_id', Request::input("slot_id"))->joinMembership()->value('binary_placement_enable');
        
		return $response;
    }
    public function get_own_membership_list()
    {
        $user_id        = Request::user()->id;
        $slot_id        = Tbl_slot::where('slot_owner', $user_id)->pluck('slot_id')->first();
        
        $is_placement_enable = Tbl_slot::where('slot_id', $slot_id)->joinMembership()->value('binary_placement_enable');

        if($is_placement_enable) {

            $response = Tbl_codes::where("code_sold_to",$user_id)
            ->where("code_used",0)
            ->Inventory()
            ->InventoryItem()
            ->InventoryItemMembership()
            ->where("binary_placement_enable", 1)
            ->CheckIfArchived()
                ->leftJoin("tbl_orders", function ($join) {
                    $join->on("tbl_orders.order_date_created", "=", "tbl_codes.code_date_sold")
                        ->orWhereRaw("ABS(TIMESTAMPDIFF(SECOND, tbl_orders.order_date_created, tbl_codes.code_date_sold)) <= 5");
                })
                ->where('tbl_orders.order_status', 'claimed')
                ->select(
                    "tbl_codes.*", 
                    "tbl_inventory.*", 
                    "tbl_item.*", 
                    "tbl_membership.*", 
                    "tbl_orders.order_id",
                    "tbl_orders.order_status"
                )
            ->get();
       
        } else {

            $response = Tbl_codes::where("code_sold_to",$user_id)
                ->where("code_used",0)
                ->Inventory()
                ->InventoryItem()
                ->InventoryItemMembership()
                ->where("binary_placement_enable", 0)
                ->CheckIfArchived()
                ->leftJoin("tbl_orders", function ($join) {
                    $join->on("tbl_orders.order_date_created", "=", "tbl_codes.code_date_sold")
                        ->orWhereRaw("ABS(TIMESTAMPDIFF(SECOND, tbl_orders.order_date_created, tbl_codes.code_date_sold)) <= 5");
                })
                ->where('tbl_orders.order_status', 'claimed')
                ->select(
                    "tbl_codes.*", 
                    "tbl_inventory.*", 
                    "tbl_item.*", 
                    "tbl_membership.*", 
                    "tbl_orders.order_id",
                    "tbl_orders.order_status"
                )
                ->get();
        }

        return $response;
    }
}
