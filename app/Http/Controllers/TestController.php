<?php

namespace App\Http\Controllers;

use App\Globals\Seed;
use App\Globals\Wizard;
use App\Globals\MlmSettings;
use App\Globals\Code;
use App\Globals\Item;
use App\Globals\MLM;
use App\Globals\Mlm_complan_manager_repurchase;
use App\Globals\Log;
use App\Globals\Digima;
use App\Globals\CronFunction;
use App\Globals\Slot;
use App\Globals\Eloading;
use App\Globals\Audit_trail;
use App\Globals\Special_plan;

use App\Models\User;
use App\Models\Tbl_slot;
use App\Models\Tbl_mlm_board_placement;
use App\Models\Tbl_other_settings;
use App\Models\Tbl_audit_trail;
use App\Models\Tbl_currency;
use App\Models\Tbl_label;
use App\Models\Tbl_wallet_log;
use App\Models\Tbl_mlm_plan;
use App\Models\Tbl_adjust_wallet_log;

use Carbon\Carbon;
use Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;
use Excel;
use Schema;
use Hash;
use Illuminate\Support\Facades\Validator;


class TestController extends Controller
{
    public function test_data()
    {
        // $slot_info = Tbl_slot::where("slot_no",)->first();
        // Mlm_complan_manager_repurchase::binary_repurchase($slot_info, 300);
        dd(123131);
        $data = DB::table("tbl_wallet_log")->select('wallet_log_slot_id','wallet_log_amount',"wallet_log_date_created", DB::raw('count(*) as ctr'))
                                           ->groupBy('wallet_log_slot_id','wallet_log_amount',"wallet_log_date_created")
                                           ->where("wallet_log_details","INTEREST")
                                           ->having("ctr",'>=',2)
                                           ->get();


        echo '<table>';
        echo '    <thead>';
        echo '        <tr>';
        echo '            <th style="text-align:center">Counter</th>';
        echo '            <th style="text-align:center">Slot Id</th>';
        echo '            <th style="text-align:center">Slot No</th>';
        echo '            <th style="text-align:center">Email</th>';
        echo '            <th style="text-align:center">Owner</th>';
        echo '            <th style="text-align:center">Number of logs</th>';
        echo '            <th style="text-align:center">Date Created</th>';
        echo '            <th style="text-align:center">Amount</th>';
        echo '        </tr>';
        echo '    </thead>';
        echo '    <tbody>';

        $ctr   = 1;
        $total = 0;

        $slot_ids = $data->pluck('wallet_log_slot_id')->unique();
        $slots = Tbl_slot::whereIn('slot_id', $slot_ids)->owner()->get()->keyBy('slot_id');

        foreach($data as $d)
        {
             $slot = $slots->get($d->wallet_log_slot_id);

             if ($slot) {
                 echo '<tr>';
                 echo '<td style="text-align:center">'.$ctr.'</td>';
                 echo '<td style="text-align:center">'.$slot->slot_id.'</td>';
                 echo '<td style="text-align:center">'.$slot->slot_no.'</td>';
                 echo '<td style="text-align:center">'.$slot->email.'</td>';
                 echo '<td style="text-align:center">'.$slot->first_name." ".$slot->middle_name." ".$slot->last_name.'</td>';
                 echo '<td style="text-align:center">'.$d->ctr.'</td>';
                 echo '<td style="text-align:center">'.$d->wallet_log_date_created.'</td>';
                 echo '<td style="text-align:center">'.$d->wallet_log_amount.'</td>';
                 echo '</tr>';
                 
                 $total = $total + ($d->wallet_log_amount * ( $d->ctr - 1));
                 $ctr++;
             }
        }  

        echo '    </tbody>';
        echo '</table>';    
        echo '</br> TOTAL: '.$total;

        dd("End");
        dd($data);
        // $data = Tbl_audit_trail::where("audit_trail_id",383)->first();
        dd($data);
        // dd(unserialize($data->old_value));
        // Special_plan::sponsor_matching(1,1000,Carbon::now());
        // Special_plan::mentors_bonus(2,100,Carbon::now());
        // dd(321);
        // MLM::purchase(2,1);
        dd(123);
        // dd($test);
    }

    public function open_import_slots()
    {
        if(Request::input("password") == "great123")
        {    
            $rowCount           = Request::input('row_count');

            if($rowCount == 'null')
            {
                $file                           = Request::file('file_data')->getRealPath();
                $check_rows = $_data            = Excel::selectSheetsByIndex(0)->load($file, function($reader){})->all();
                $return['total']                = $check_rows->count();
                $return['current']              = 0;

                DB::table("tbl_importation_data")->delete();

                $insert["importation_data"] = serialize($_data);
                DB::table("tbl_importation_data")->insert($insert);

                return response()->json($return);
            }
            else
            {
                $row_count      = intval($rowCount);
                $file           = Request::file('file_data')->getRealPath();
                $data           = unserialize(DB::table("tbl_importation_data")->first()->importation_data);

                if(isset($data[$row_count]))
                {
                    $import_settings["rematrix"]            = Request::input("rematrix");   
                    $import_settings["reentry"]             = Request::input("reentry");
                    $import_settings["reset_wallet"]        = Request::input("reset_wallet");   
                    $import_settings["reset_points"]        = Request::input("reset_points");
                    $import_settings["create_if_not_exist"] = Request::input("create_if_not_exist");

                    $process = Slot::import_slots($data[$row_count],$import_settings);

                    $return['process_returned']                  = $process["process_returned"];

                    $return['finished_data']["slot_no"]          = $data[$row_count]["slot_no"];
                    $return['finished_data']["email"]            = $data[$row_count]["email"];
                    // $return['finished_data']["first_name"]       = $data[$row_count]["first_name"];
                    // $return['finished_data']["middle_initial"]   = $data[$row_count]["middle_initial"];
                    // $return['finished_data']["last_name"]        = $data[$row_count]["last_name"];
                    $return['finished_data']["sponsor"]          = $data[$row_count]["sponsor"];
                    $return['finished_data']["placement"]        = $data[$row_count]["placement"];
                    $return['finished_data']["position"]         = $data[$row_count]["position"];
                    $return['finished_data']["status"]           = $process["process_returned"];

                    if($process["process_returned"] == "Success")
                    {
                        $return['finished_data']["status_message"]  = "----";
                    }
                    else
                    {
                        $append = "";
                        $total  = count($process["process_message"]);

                        $ctr    = 1;

                        foreach($process["process_message"] as $process_message)
                        {
                            if($total == 1)
                            {
                                $append = $append . $process_message;
                            }
                            else
                            {
                                if($ctr == $total)
                                {
                                    $append = $append.$process_message;
                                }
                                else
                                {
                                    $append = $append.$process_message.",";
                                }
                            }

                            $ctr++;
                        }

                        $return['finished_data']["status_message"] = $append;
                    }

                    
                    $row_count          = $row_count + 1;
                    $return['current']  = $row_count;

                    return response()->json($return);
                }
                else
                {

                    $return["status"]         = "Complete"; 
                    $return["status_code"]    = 200; 
                    $return["status_message"] = "IMPORTED SUCCESSFULLY";
                    return response()->json($return);
                }
            }
        }
    }

    public function test_re_entry()
    {
        $check_settings = Tbl_other_settings::where("key","test_re_entry")->first() ? Tbl_other_settings::where("key","test_re_entry")->first()->value : 0;
        if($check_settings == 1)
        {
            $slot_id = Request::input("slot_id");
            $slot    = Tbl_slot::where("slot_id",$slot_id)->first();
            if($slot)
            {
                MLM::create_entry($slot_id);
                dd("RE ENTRY SUCCESS");
            }
            else
            {
                dd("INVALID SLOT");
            }
        }
        else
        {
            dd("TEST RE ENTRY IS DISABLED");
        }
    }

    public function archive()
    {
        $codes = Tbl_codes::join('tbl_code_transfer_logs','tbl_code_transfer_logs.code_id','=', 'tbl_codes.code_id')->get();
        $ctr = 0;
        foreach($codes as $key => $value)
        {
            if($value->code_date_used != null && strtotime($value->code_date_used) < strtotime($value->date_transfer))
            {
                // $update['data'][$ctr] = $value;
                $update[$ctr]   = $value;
                $ctr = $ctr + 1;
                $archive['archived']    = 1;
                Tbl_codes::where('code_id', $value->code_id)->update($archive);
            }
        }




        //export
        // $update['header']  =   ['Code ID','Activation','PIN','Date Sold','Date Used', 'Date Transferred'];
        // Excel::create('Codes', function($excel) use ($update)
        // {
        //     $excel->sheet('template', function($sheet) use ($update)
        //     {
        //         $data = $update['header'];
        //         $sheet->fromArray($data, null, 'A1', false, false);
        //         $sheet->freezeFirstRow();
        //         foreach($update['data'] as  $key => $list)
        //         {
        //             $key = $key+=2;
        //             $sheet->setCellValue('A'.$key, $list->code_id);
        //             $sheet->setCellValue('B'.$key, $list->code_activation);
        //             $sheet->setCellValue('C'.$key, $list->code_pin);
        //             $sheet->setCellValue('D'.$key, $list->code_date_sold);
        //             $sheet->setCellValue('E'.$key, $list->code_date_used);
        //             $sheet->setCellValue('F'.$key, $list->date_transfer);
        //         }
        //     });
        // })->download('xls');
        
    }


    public function remove_space()
    {
        $slots = Tbl_slot::select('slot_no','slot_id')->get();
        foreach($slots as $key => $value)
        {
            $s = str_replace(' ', '', $value['slot_no']);
            $update['slot_no'] = $s;
            Tbl_slot::where('slot_id', $value['slot_id'])->update($update);
        }
    }

    public function add_item()
    {
        $x = 0;
        $receipt_table = DB::table('tbl_receipt')->select('items', 'receipt_id')->get();
        foreach($receipt_table as $key => $value)
        {
            foreach($value as $key2 => $value2)
            {
                if($key2 == 'items')
                {
                    $item = json_decode($value2);
                    foreach($item as $key3 => $value3)
                    {
                        if(isset($value3->item_id))
                        {
                            $insert['rel_receipt_id'] = $value->receipt_id;
                            $insert['item_id'] = $value3->item_id;
                            $insert['quantity'] = $value3->quantity;
                            DB::table('tbl_receipt_rel_item')->insert($insert);
                        }
                        else
                        {
                            if($key3 == 'item_id')
                            {
                                $insert2['rel_receipt_id'] = $value->receipt_id;
                                $insert2['item_id'] = $value3;
                                
                            }
                            if($key3 == 'quantity')
                            {
                                $insert2['quantity'] = $value3;
                            }
                            $x = 1;
                        }
                    }
                    if($x > 0)
                    {
                        DB::table('tbl_receipt_rel_item')->insert($insert2);
                        $x = 0;
                    }
                }
            }
        }
        $order_table = DB::table('tbl_orders')->select('items', 'order_id')->get();
        foreach($order_table as $key => $value)
        {
            foreach($value as $key2 => $value2)
            {
                if($key2 == 'items')
                {
                    $item = json_decode($value2);
                    foreach($item as $key3 => $value3)
                    {
                        if(isset($value3->item_id))
                        {
                            $insert_order['rel_order_id'] = $value->order_id;
                            $insert_order['item_id'] = $value3->item_id;
                            $insert_order['quantity'] = $value3->quantity;
                            DB::table('tbl_orders_rel_item')->insert($insert_order);
                        }
                        else
                        {
                            if($key3 == 'item_id')
                            {
                                $insert_order2['rel_order_id'] = $value->order_id;
                                $insert_order2['item_id'] = $value3;
                                
                            }
                            if($key3 == 'quantity')
                            {
                                $insert_order2['quantity'] = $value3;
                            }
                            $x = 1;
                        }
                    }
                    if($x > 0)
                    {
                        DB::table('tbl_orders_rel_item')->insert($insert_order2);
                        $x = 0;
                    }
                }
            }
        }
    }

    public function seed()
    {
        return response()->json(Seed::initial_seed());
        // echo "done";
    }

    public function compute_payment()
    {
        $orders = DB::table('tbl_orders')->where('order_from', 'cashier')->get();
        
        foreach($orders as $key=> $value)
        {
            $update['payment_tendered'] = $value->grand_total + $value->change;
            DB::table('tbl_orders')->where('order_id', $value->order_id)->update($update);
        }
    }

    public function wizard_one()
    {
        $insert["admin_username"]               = "Sample";
        $insert["admin_password"]               = "123123";
        $insert["admin_rpassword"]              = "123123";
        $insert["admin_first_name"]             = "Erwin";
        $insert["admin_last_name"]              = "Guevarra";
        $insert["admin_email"]                  = "guevarra129@gmail.com";
        $insert["admin_contact"]                = "09354666344";
        $insert["admin_date_created"]           = Carbon::now();

        $insert_company["company_name"]         = "Sample Company";
        $insert_company["company_contact"]      = "8785641113";
        $insert_company["company_address"]      = "Sample Address";
        $insert_company["company_office_hours"] = "8 hours";

        dd(Wizard::step_one($insert,$insert_company));
    }

    public function wizard_two()
    {
        $insert["country_id"]               = 1;
        $insert["base_currency"]            = 1;
        $insert["allow_multiple_currency"]  = 1;

        // 1 / 2 is ID of a country samples only
        $insert_country[1] = 1;
        $insert_country[2] = 50;

        dd(Wizard::step_two($insert,$insert_country));
    }

    public function wizard_three()
    {
        $insert["binary_enabled"]               = 1;
        $insert["auto_placement"]               = 1;
        $insert["auto_placement_type"]          = "left_to_right"; 
        $insert["member_disable_auto_position"] = 1; 
        $insert["member_default_position"]      = 1; 
        $insert["mlm_slot_no_format_type"]      = 1; 

        dd(Wizard::step_three($insert));
    }

    public function wizard_four()
    {
        $insert["free_registration"]            = 1;
        $insert["multiple_type_membership"]     = 1;
        $insert["gc_inclusive_membership"]      = 1;
        $insert["product_inclusive_membership"] = 1;


        dd(Wizard::step_four($insert));
    }

    public function wizard_five()
    {
        $insert[1] = "BINARY";
        $insert[2] = "DIRECT";


        dd(Wizard::step_five($insert));
    }

    public function wizard_five_one()
    {
        $insert[0]["membership_id"]              = 1;
        $insert[0]["membership_entry_id"]        = 1;
        $insert[0]["membership_direct_income"]   = 500;

        $insert[1]["membership_id"]              = 1;
        $insert[1]["membership_entry_id"]        = 2;
        $insert[1]["membership_direct_income"]   = 500;

        $insert[2]["membership_id"]              = 1;
        $insert[2]["membership_entry_id"]        = 3;
        $insert[2]["membership_direct_income"]   = 1000;

        $insert[2]["membership_id"]              = 2;
        $insert[2]["membership_entry_id"]        = 1;
        $insert[2]["membership_direct_income"]   = 1000;



        dd(Wizard::step_five_one($insert));
    }

    public function wizard_five_two()
    {
        $insert[0]["membership_level"]           = 1;
        $insert[0]["membership_id"]              = 1;
        $insert[0]["membership_entry_id"]        = 1;
        $insert[0]["membership_indirect_income"] = 500;

        $insert[1]["membership_level"]           = 1;
        $insert[1]["membership_id"]              = 1;
        $insert[1]["membership_entry_id"]        = 2;
        $insert[1]["membership_indirect_income"] = 600;

        $insert[2]["membership_level"]           = 1;
        $insert[2]["membership_id"]              = 1;
        $insert[2]["membership_entry_id"]        = 3;
        $insert[2]["membership_indirect_income"] = 700;



        dd(Wizard::step_five_two($insert));
    }

    public function wizard_five_three()
    {
        $insert_plan_setting["strong_leg_retention"]    = 0;
        $insert_plan_setting["gc_pairing_count"]        = 5;
        $insert_plan_setting["cycle_per_day"]           = 1;
 
        $insert[0]["membership_id"]                     = 1;
        $insert[0]["membership_entry_id"]               = 1;
        $insert[0]["membership_binary_points"]          = 500;
 
        $insert[1]["membership_id"]                     = 1;
        $insert[1]["membership_entry_id"]               = 2;
        $insert[1]["membership_binary_points"]          = 500;
 
        $insert[2]["membership_id"]                     = 1;
        $insert[2]["membership_entry_id"]               = 3;
        $insert[2]["membership_binary_points"]          = 1000;
 
        $insert[2]["membership_id"]                     = 2;
        $insert[2]["membership_entry_id"]               = 1;
        $insert[2]["membership_binary_points"]          = 1000;

        $insert_combination[0]["binary_pairing_left"]   = 1;
        $insert_combination[0]["binary_pairing_right"]  = 1;
        $insert_combination[0]["binary_pairing_bonus"]  = 500;

        $insert_combination[1]["binary_pairing_left"]   = 2;
        $insert_combination[1]["binary_pairing_right"]  = 2;
        $insert_combination[1]["binary_pairing_bonus"]  = 1000;

        dd(Wizard::step_five_three($insert_plan_setting,$insert,$insert_combination));
    }

    public function wizard_five_four()
    {
        $data["personal_as_group"]         = 0;
        $data["gpv_to_wallet_conversion"]  = 5;
 
        $data_membership[0]["membership_id"]           = 1;
        $data_membership[0]["membership_required_pv"]  = 1;


        dd(Wizard::step_five_four($data,$data_membership));
    }

    public function wizard_five_five()
    {
        $data["personal_as_group"] = 1;
        $data["live_update"]       = 1;
        $data["allow_downgrade"]   = 1;
        $data["rank_first"]        = 1;


        dd(Wizard::step_five_five($data));
    }

    public function unilevel_save_settings()
    {
        $data[0]["membership_level"]      = 1;   
        $data[0]["membership_id"]         = 1;       
        $data[0]["membership_entry_id"]   = 1;   
        $data[0]["membership_percentage"] = 39;  

        $data[1]["membership_level"]      = 2;   
        $data[1]["membership_id"]         = 1;       
        $data[1]["membership_entry_id"]   = 1;   
        $data[1]["membership_percentage"] = 20;    

        dd(MlmSettings::unilevel_save_settings($data));
    }

    public function digima()
    {
        dd(Digima::addRequest(1));
    }
    public function address_seed()
    {
        // if(Schema::hasTable('refcitymun'))
        // {
        //     $message[1] = "meron ng city";
        // }
        // else
        // {
        //     DB::unprepared(file_get_contents('sql/refCitymun.sql'));
        // }

        // if(Schema::hasTable('refbrgy'))
        // {
        //     $message[0] = "meron ng barangay";
        // }
        // else
        // {
        //     DB::unprepared(file_get_contents('sql/refBrgy.sql'));
        // }
        
        // if(Schema::hasTable('refprovince'))
        // {
        //     $message[2] = "meron ng province";
        // }
        // else
        // {
        //     DB::unprepared(file_get_contents('sql/refProvince.sql'));
        // }
        // if(Schema::hasTable('refregion'))
        // {
        //     $message[3] = "meron ng region";
        // }
        // else
        // {
        //     DB::unprepared(file_get_contents('sql/refRegion.sql'));
        // }

        DB::unprepared(file_get_contents('sql/refCitymun.sql'));
        DB::unprepared(file_get_contents('sql/refBrgy.sql'));
        DB::unprepared(file_get_contents('sql/refProvince.sql'));
        DB::unprepared(file_get_contents('sql/refRegion.sql'));

        dd("force seed");
    }
    public function add_usd($id)
    {
        Log::insert_wallet($id, 1,"SENDING FUNDS", 2);
    }
}
