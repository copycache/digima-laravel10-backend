<?php
namespace App\Http\Controllers\Member;

use App\Globals\Slot;
use App\Globals\Branch;

use App\Models\Tbl_binary_points;
use App\Models\Tbl_earning_log;
use App\Models\Tbl_slot;
use App\Models\Tbl_mlm_board_slot;
use App\Models\Tbl_mlm_board_placement;
use App\Models\Tbl_mlm_board_settings;
use App\Models\Tbl_top_recruiter;


use App\Models\Tbl_tree_placement;
use App\Models\Tbl_tree_sponsor;
use App\Models\Tbl_genealogy_settings;
use App\Models\Tbl_dynamic_compression_record;
use App\Models\Tbl_unilevel_points;
use App\Models\Tbl_mlm_unilevel_settings;
use App\Models\Tbl_membership;
use App\Models\Tbl_matrix_placement;

use Carbon\Carbon;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;

class MemberGenealogyController extends MemberController
{

    public static function count_child($placement_parent_id)
    {
        $count['left']      = Tbl_tree_placement::where('placement_parent_id',$placement_parent_id)->where('placement_position','LEFT')->count();
        $count['right']     = Tbl_tree_placement::where('placement_parent_id',$placement_parent_id)->where('placement_position','RIGHT')->count();
        return $count;
    }

    public static function count_child_matrix($placement_parent_id)
    {
        $count['left']      = Tbl_matrix_placement::where('parent_id',$placement_parent_id)->where('position','LEFT')->count();
        $count['right']     = Tbl_matrix_placement::where('parent_id',$placement_parent_id)->where('position','RIGHT')->count();
        return $count;
    }

    public static function count_board_child($placement_parent_id, $board_level)
    {
        $count['left']      = Tbl_mlm_board_placement::where('placement_parent_id',$placement_parent_id)->where('board_level', $board_level)->where('placement_position','LEFT')->count();
        $count['right']     = Tbl_mlm_board_placement::where('placement_parent_id',$placement_parent_id)->where('board_level', $board_level)->where('placement_position','RIGHT')->count();
        return $count;
    }

    public function placement()
    {
        $root_slot                      = Request::input("root_slot");
        $placement                      = Request::input("placement");
        $slot                           = Tbl_slot::where("slot_placement",$placement)->first();
        $slot_placement                 = Tbl_slot::where("slot_id",$placement)->first();
        $settings                       = self::what_show();
        $cycle                          = DB::table("tbl_binary_settings")->first() ? DB::table("tbl_binary_settings")->first()->cycle_per_day : 1;
        $data["left"]                   = Tbl_slot::where("slot_placement",$placement)->owner()->JoinMembership()->where("slot_position","LEFT")->first();
        if($data["left"])
        {
            $sponsor_id                         = Tbl_slot::where('slot_id',$data["left"]->slot_id)->pluck('slot_sponsor')->first();
            $data["left"]->total_recruits       = Tbl_slot::where('slot_sponsor',$data["left"]->slot_id)->count();
            $data["left"]->binary_count         = Self::count_child($data["left"]->slot_id);
            $data["left"]->settings             =  $settings;
            $data["left"]->sponsor_username     = Tbl_slot::where('slot_id',$sponsor_id)->pluck('slot_no')->first();
            $data['left']->max_pairs      =  Tbl_slot::where('slot_id', $data["left"]->slot_id)->JoinMembership()->first() ? Tbl_slot::where('slot_id', $data["left"]->slot_id)->JoinMembership()->first()->membership_pairings_per_day : 0;
            $data['left']->todays_pairs  = Tbl_earning_log::where('earning_log_slot_id', $data["left"]->slot_id)->where('earning_log_plan_type','=','BINARY');
            $data['left']->accumulated_left_points = Tbl_binary_points::where('binary_points_slot_id', $data["left"]->slot_id)->where('binary_receive_left', '>=', 0)->sum('binary_receive_left');
		    $data['left']->accumulated_right_points = Tbl_binary_points::where('binary_points_slot_id', $data["left"]->slot_id)->where('binary_receive_right', '>=', 0)->sum('binary_receive_right');

            if($cycle == 1)
            {
                $today = Carbon::now()->format('Y-m-d');
                $data['left']->todays_pairs = $data['left']->todays_pairs->wheredate('earning_log_date_created',$today)->count();
            }
            else if($cycle == 2)
            {
                // dd($data['todays_pairs']->count());
                $meridiem = Carbon::now()->format('A');
                if($meridiem == "AM")
                {
                    $today = Carbon::now()->format('Y-m-d 00:00:00');
                    $data['left']->todays_pairs = $data['left']->todays_pairs->wheredate('earning_log_date_created','>=',$today)->count();
                }
                else 
                {
                    $today = Carbon::now()->format('Y-m-d 12:00:00');
                    $data['left']->todays_pairs = $data['left']->todays_pairs->wheredate('earning_log_date_created','<=',$today)->count();
                }
            }
            else 
            {
                $start = Carbon::now()->startofWeek();
                $end = Carbon::now()->endofWeek();
                $data['left']->todays_pairs = $data['left']->todays_pairs->wheredate('earning_log_date_created',">=",$start)->wheredate('earning_log_date_created',"<=",$end)->count();
            }
        }
        $data["right"]                          = Tbl_slot::where("slot_placement",$placement)->owner()->JoinMembership()->where("slot_position","RIGHT")->first();

        if($data['right'])
        {
            $sponsor_id                         = Tbl_slot::where('slot_id',$data["right"]->slot_id)->pluck('slot_sponsor')->first();
            $data["right"]->total_recruits      = Tbl_slot::where('slot_sponsor',$data["right"]->slot_id)->count();
            $data["right"]->binary_count        = Self::count_child($data["right"]->slot_id);
            $data["right"]->settings            =  $settings;
            $data["right"]->sponsor_username    = Tbl_slot::where('slot_id',$sponsor_id)->pluck('slot_no')->first();
            $data['right']->max_pairs      =  Tbl_slot::where('slot_id',$data["right"]->slot_id)->JoinMembership()->first() ? Tbl_slot::where('slot_id',$data["right"]->slot_id)->JoinMembership()->first()->membership_pairings_per_day : 0;
            $data['right']->todays_pairs  = Tbl_earning_log::where('earning_log_slot_id',$data["right"]->slot_id)->where('earning_log_plan_type','=','BINARY');
            $data['right']->accumulated_left_points	= Tbl_binary_points::where('binary_points_slot_id',$data["right"]->slot_id)->sum('binary_receive_left');
		    $data['right']->accumulated_right_points			= Tbl_binary_points::where('binary_points_slot_id',$data["right"]->slot_id)->sum('binary_receive_right');

            if($cycle == 1)
            {
                $today = Carbon::now()->format('Y-m-d');
                $data['right']->todays_pairs = $data['right']->todays_pairs->wheredate('earning_log_date_created',$today)->count();
            }
            else if($cycle == 2)
            {
                // dd($data['todays_pairs']->count());
                $meridiem = Carbon::now()->format('A');
                if($meridiem == "AM")
                {
                    $today = Carbon::now()->format('Y-m-d 00:00:00');
                    $data['right']->todays_pairs = $data['right']->todays_pairs->wheredate('earning_log_date_created','>=',$today)->count();
                }
                else 
                {
                    $today = Carbon::now()->format('Y-m-d 12:00:00');
                    $data['right']->todays_pairs = $data['right']->todays_pairs->wheredate('earning_log_date_created','<=',$today)->count();
                }
            }
            else 
            {
                $start = Carbon::now()->startofWeek();
                $end = Carbon::now()->endofWeek();
                $data['right']->todays_pairs = $data['right']->todays_pairs->wheredate('earning_log_date_created',">=",$start)->wheredate('earning_log_date_created',"<=",$end)->count();
            }
        }



        $data['binary_count'] = Self::count_child($root_slot);

        $data["placement"] = $slot_placement->slot_no;
        if($slot)
        {
           $data["level"]     = Tbl_tree_placement::where("placement_parent_id",$root_slot)->where("placement_child_id",$slot->slot_id)->first() ? Tbl_tree_placement::where("placement_parent_id",$root_slot)->where("placement_child_id",$slot->slot_id)->first()->placement_level : 0;
        }
        return json_encode($data);
    }

    public function matrix()
    {
        $root_slot                      = Request::input("root_slot");
        $placement                      = Request::input("placement");
        $slot                           = Tbl_slot::where("matrix_sponsor",$placement)->first();
        $slot_placement                 = Tbl_slot::where("slot_id",$placement)->first();
        $settings                       = self::what_show();
        $data["left"]                   = Tbl_slot::where("matrix_sponsor",$placement)->owner()->JoinMembership()->where("matrix_position","LEFT")->first();
        if($data["left"])
        {
            $sponsor_id = Tbl_slot::where('slot_id',$data["left"]->slot_id)->pluck('slot_sponsor')->first();
            $data["left"]->total_recruits = Tbl_slot::where('slot_sponsor',$data["left"]->slot_id)->count();
            
            $data["left"]->matrix_count = Self::count_child_matrix($data["left"]->slot_id);

            $data["left"]->settings =  $settings;
            $data["left"]->sponsor_username = Tbl_slot::where('slot_id',$sponsor_id)->pluck('slot_no')->first();
        }
        $data["right"]                          = Tbl_slot::where("matrix_sponsor",$placement)->owner()->JoinMembership()->where("matrix_position","RIGHT")->first();

        if($data['right'])
        {
            $sponsor_id                         = Tbl_slot::where('slot_id',$data["right"]->slot_id)->pluck('slot_sponsor')->first();
            $data["right"]->total_recruits      = Tbl_slot::where('slot_sponsor',$data["right"]->slot_id)->count();
            $data["right"]->matrix_count        = Self::count_child_matrix($data["right"]->slot_id);
            $data["right"]->settings            =  $settings;
            $data["right"]->sponsor_username    = Tbl_slot::where('slot_id',$sponsor_id)->pluck('slot_no')->first();
        }

        $data['matrix_count'] = Self::count_child_matrix($root_slot);

        $data["placement"] = $slot_placement->slot_no;
        // if($slot)
        // {
        //    $data["level"]     = Tbl_tree_placement::where("placement_parent_id",$root_slot)->where("placement_child_id",$slot->slot_id)->first() ? Tbl_tree_placement::where("placement_parent_id",$root_slot)->where("placement_child_id",$slot->slot_id)->first()->placement_level : 0;
        // }
        return json_encode($data);
    }

    public function board()
    {
        $level                          = Request::input("board_level");
        $board_level                    = (int)$level;
        $root_slot                      = Request::input("root_slot");
        $placement                      = Request::input("placement");
        $board_settings                 = Tbl_mlm_board_settings::first();
        $slot                           = Tbl_mlm_board_slot::where("placement",$placement)->where('board_level', $board_level)->first();
        $slot_placement                 = Tbl_mlm_board_slot::where("tbl_mlm_board_slot.slot_id",$placement)->join('tbl_slot', 'tbl_slot.slot_id', '=', 'tbl_mlm_board_slot.slot_id')->where('board_level', $board_level)->first();
        $settings                       = self::what_show();
        // dd($slot_placement);

        $data["left"]                   = Tbl_mlm_board_slot::where("placement",$placement)->owner()->JoinMembership()->where('board_level', $board_level)->where("placement_position","LEFT")->select("last_name","first_name","tbl_mlm_board_slot.slot_id","slot_no","placement_position","placement","color")->first();

        if($data["left"])
        {
            $data["left"]->total_recruits =Tbl_slot::where('slot_sponsor',$data["left"]->slot_id)->count();
            $data["left"]->binary_count = Self::count_board_child($data["left"]->slot_id,$board_level);
            $data["left"]->settings         =  $settings;
        }
        $data["right"]                  = Tbl_mlm_board_slot::where("placement",$placement)->owner()->JoinMembership()->where('board_level', $board_level)->where("placement_position","RIGHT")->select("last_name","first_name","tbl_mlm_board_slot.slot_id","slot_no","placement_position","placement","color")->first();
        if($data['right'])
        {
            $data["right"]->total_recruits = Tbl_slot::where('slot_sponsor',$data["right"]->slot_id)->count();
            $data["right"]->binary_count = Self::count_board_child($data["right"]->slot_id,$board_level);
            $data["right"]->settings    =  $settings;

        }
        $data['binary_count'] = Self::count_board_child($root_slot,$board_level);

        // $data["placement"] = $slot_placement->slot_no;
        $data["placement"] = "root";

        if($slot)
        {
            $data["level"]     = Tbl_mlm_board_placement::where("placement_parent_id",$root_slot)->where('board_level', $board_level)->where("placement_child_id",$slot->slot_id)->first() ? Tbl_mlm_board_placement::where("placement_parent_id",$root_slot)->where("placement_child_id",$slot->slot_id)->where('board_level', $board_level)->first()->placement_level : 0;
        }
        // dd($data);
        return json_encode($data);



    }

    public function unilevel()
    {
        $root_slot           = Request::input("root_slot");
        $sponsor     		 = Request::input("placement");
        $slot      		     = Tbl_slot::where("slot_sponsor",$sponsor)->first();
        $slot_sponsor        = Tbl_slot::where("slot_id",$sponsor)->first();
        $settings            = self::what_show();
        //Sir Elven code
        //$data["_slot"]       = Tbl_tree_sponsor::where('sponsor_parent_id',$sponsor)->child()->owner()->select("last_name","first_name","slot_id","slot_no","slot_position","slot_placement")->orderBy('tree_sponsor_id','ASC')->get();

        //Original code
        //$data["_slot"]       = Tbl_slot::where("slot_sponsor",$sponsor)->owner()->where('slot_type','PS')->select("last_name","first_name","slot_id","slot_no","slot_position","slot_placement")->get();

        //New code
        $data["_slot"] = Tbl_slot::where("slot_sponsor",$sponsor)->owner()->JoinMembership()->where('slot_type','PS')->treesponsor()->where('sponsor_parent_id',$sponsor)->orderBy('tree_sponsor_id','ASC')->get();
        foreach ($data["_slot"] as $key => $value)
        {
            $data["_slot"][$key]->total_recruits = Tbl_slot::where('slot_sponsor',$value->slot_id)->count();
            $data["_slot"][$key]->settings    =  $settings;
            $data["_slot"][$key]->is_dynamic  =  Tbl_mlm_unilevel_settings::first()->is_dynamic;
            $slot                    = Tbl_slot::where("slot_id",$value->slot_id)->first();
            $total_ppv               = 0;
            if($slot)
            {
                $membership                            = Tbl_membership::where("membership_id",$slot->slot_membership)->first();
                if($membership)
                {
                    $membership->membership_unilevel_level = $membership->membership_unilevel_level;
                    $level                                 = 1;
                    $first_date                      = Carbon::now()->startOfMonth()->format("Y-m-d");
                    $end_date                        = Carbon::now()->endOfMonth()->format("Y-m-d");

                    $pluss          = Tbl_unilevel_points::where("unilevel_points_slot_id",$slot->slot_id)->where("unilevel_points_type","UNILEVEL_PPV")->where("unilevel_points_date_created",">=",$first_date)->where("unilevel_points_date_created","<=",$end_date)->sum("unilevel_points_amount");
                    $total_ppv = $total_ppv + $pluss;
                }
            }
            $data["_slot"][$key]->slot_personal_pv     = number_format($total_ppv,0);
            $data["_slot"][$key]->sponsor_username     = Tbl_slot::where('slot_id',$slot->slot_sponsor)->pluck('slot_no')->first();
        }

        $data["placement"]          = $slot_sponsor;
        // dd($data);
        if($slot)
        {
           $data["level"]     = Tbl_tree_sponsor::where("sponsor_parent_id",$root_slot)->where("sponsor_child_id",$slot->slot_id)->first() ? Tbl_tree_sponsor::where("sponsor_parent_id",$root_slot)->where("sponsor_child_id",$slot->slot_id)->first()->sponsor_level : 0;
        }

        return json_encode($data);
    }

    public function get_placement_downline()
    {
        $filter = Request::input();
        
        $query  = Tbl_tree_placement::where("placement_parent_id",$filter["slot_id"])->child()->Owner()->Membership()->select("slot_id","placement_child_id","slot_no","slot_sponsor","slot_placement","slot_date_placed","placement_level","placement_position","placement_level","first_name","middle_name","last_name","email","contact","membership_name","name");
        if(isset($filter['search']))
        {
            $query = $query->where('name', 'like', '%' . $filter['search'] . '%')->orWhere('slot_no', 'like', '%' . $filter['search'] . '%' );
        }
        $return = $query->paginate(15);

        foreach($return as $key => $value)
        {
            $return[$key]->sponsor_name = Tbl_slot::join('users','users.id','=','tbl_slot.slot_owner')->where('slot_id', $value->slot_sponsor)->value('name');
            $return[$key]->placement_name = Tbl_slot::join('users','users.id','=','tbl_slot.slot_owner')->where('slot_id', $value->slot_placement)->value('name');
            $return[$key]->placement_slotno = Tbl_slot::join('users','users.id','=','tbl_slot.slot_owner')->where('slot_id', $value->slot_placement)->value('slot_no');

        }
        return $return;

    }

    public function get_matrix_downline()
    {
        $filter = Request::input();
        
        $query  = Tbl_matrix_placement::where("parent_id",$filter["slot_id"])->child()->Owner()->Membership()->select("slot_id","child_id","slot_no","slot_sponsor","matrix_sponsor","slot_date_placed","level","position","level","first_name","middle_name","last_name","email","contact","membership_name","name");
        
        if(isset($filter['search']))
        {
            $query = $query->where('name', 'like', '%' . $filter['search'] . '%')->orWhere('slot_no', 'like', '%' . $filter['search'] . '%' );
        }
        $return = $query->orderBy('matrix_id', 'desc')->paginate(15);

        foreach($return as $key => $value)
        {
            $return[$key]->sponsor_name = Tbl_slot::join('users','users.id','=','tbl_slot.slot_owner')->where('slot_id', $value->slot_sponsor)->value('name');
            $return[$key]->matrix_name = Tbl_slot::join('users','users.id','=','tbl_slot.slot_owner')->where('slot_id', $value->matrix_sponsor)->value('name');
            $return[$key]->matrix_slotno = Tbl_slot::join('users','users.id','=','tbl_slot.slot_owner')->where('slot_id', $value->matrix_sponsor)->value('slot_no');
        }
        return $return;
    }

    public function get_unilevel_downline()
    {
        $filter = Request::input();
        $query  =Tbl_tree_sponsor::where("sponsor_parent_id",$filter["slot_id"])->child()->Owner()->Membership()->select("slot_id","sponsor_child_id","slot_no","slot_date_created","sponsor_level","first_name","middle_name","last_name","email","contact","membership_name","slot_sponsor","name");
        if(isset($filter['search']))
        {
            $query = $query->where('name', 'like', '%' . $filter['search'] . '%')->orWhere('slot_no', 'like', '%' . $filter['search'] . '%' );
        }
        $return = $query->paginate(15);
        foreach ($return as $key => $value) 
        {
           $return[$key]->sponsor_code = Tbl_slot::where("slot_id",$value->slot_sponsor)->first()->slot_no;
           $return[$key]->sponsor_name = Tbl_slot::join('users','users.id','=','tbl_slot.slot_owner')->where('slot_id', $value->slot_sponsor)->value('name');

           
        }
        return $return;
    }
    public function root_color()
    {
        $slot_id = Request::input("slot_id");
        $data["color"] = Tbl_slot::where("slot_id",$slot_id)->owner()->JoinMembership()->select("color")->first();
        return $data;
    }
    public function what_show()
    {
        $return = Tbl_genealogy_settings::first();
        if(!$return)
        {
            $update['show_full_name']       = 1;
            $update['show_slot_no']         = 1;
            $update['show_date_joined']     = 1;
            $update['show_directs_no']      = 1;
            $update['show_binary_points']   = 1;
            $update['show_maintenance_pv']  = 1;
            $update['show_membership']      = 1;
            $update['show_sponsor_username']      = 1;
            Tbl_genealogy_settings::insert($update);
        }
        $return2 = Tbl_genealogy_settings::first();
        return $return2;
    }
}
