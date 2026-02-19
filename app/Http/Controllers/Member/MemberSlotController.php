<?php
namespace App\Http\Controllers\Member;

use App\Globals\Code;
use App\Globals\Item;
use App\Globals\Slot;
use App\Globals\Member;

use App\Models\Tbl_binary_settings;
use App\Models\Tbl_slot;
use App\Models\Tbl_membership;
use App\Models\Tbl_tree_placement;

use Illuminate\Support\Facades\Request;

class MemberSlotController extends MemberController
{
    public function get_unplaced_slot()
    {
        $is_placement_enable = Tbl_slot::where('slot_id', Request::input("slot_id"))->joinMembership()->value('binary_placement_enable');
        if($is_placement_enable) {

          $reponse['status'] = 'disabled_placement';
          $reponse['message'] = 'Your membership package is not applicable for Binary Placement!';

          return $reponse;
        } else {

          $position = Member::get_strong_leg_position(Request::input("slot_id"));
          $slots = Slot::get_unplaced_slot(Request::user()->id,Request::input("slot_id"));
          $x = count($slots);
          if($x == 0)
          {
            $slots = null;
          }
          else
          {
            foreach($slots as $key => $value)
            {
              $membership = Tbl_membership::where('membership_id', $value->slot_membership)->first();

              if ($membership) { 
                if ($membership->binary_placement_enable != 0) {
                  // Remove the element from $slots
                  unset($slots[$key]);
                } else {
                    $slots[$key]->membership = $membership->membership_name;
                }
              }
            }
            $slots = array_values(json_decode(json_encode($slots), true));
          }
        }
        
        $response['slots'] = $slots;
        $response['position'] = $position;
        return json_encode($response);
    }

    public function transfer_slot()
    {
      $owner_id       = Request::user()->id;  
      $slot_id        = Request::input("slot_id");  
      $password       = Request::input("password");  
      $transferred_to = Request::input("transferred_to");     

      $response = Slot::transfer($owner_id,$slot_id,$password,$transferred_to);

      return json_encode($response);
    }

    public function get_unplaced_downline_slot()
    {
      $is_placement_enable = Tbl_slot::where('slot_id', Request::input("slot_id"))->joinMembership()->value('binary_placement_enable');
        if($is_placement_enable) {
          $slots = null;
        }
        else {
          $slots = Slot::get_unplaced_downline_slot(Request::user()->id,Request::input("slot_id"));
          $x = count($slots);
          if($x == 0)
          {
            $slots = null;
          }
          else
          {
            foreach($slots as $key => $value)
            {
              $membership = Tbl_membership::where('membership_id', $value->slot_membership)->first();

              if ($membership) { 
                if ($membership->binary_placement_enable != 0) {
                  // Remove the element from $slots
                  unset($slots[$key]);
                } else {
                    $slots[$key]->membership = $membership->membership_name;
                }
              }
            }
            $slots = array_values(json_decode(json_encode($slots), true));
          }
        }
        return json_encode($slots);
    }


    public function place_own_slot()
    {

       $data["slot_placement"]  = Request::input("placement");
       $data["slot_position"]   = Request::input("position");
       $data["slot_code"]       = Request::input("slot_no");


      $response              = Slot::place_slot($data,"member_owned",Request::user()->id);
      return $response;
    }

    public function get_unactivated_slot()
    {
      $response = 0;
      $check = Tbl_slot::where("membership_inactive",1)->where("slot_owner",Request::user()->id)->where('slot_no',Request::input('slot_no'))->where("slot_status","active")->first();
      if($check)
      {
        $response = 1;
      }

      return $response; 
    }


    public function place_downline_slot()
    {

       $data["slot_placement"]  = Request::input("placement");
       $data["slot_position"]   = Request::input("position");
       $data["slot_code"]       = Request::input("slot_no");


        $response              = Slot::place_slot($data,"member_downline",Request::user()->id);
        return $response;
    }

    public function place_downline_slot_other_info()
    {

      $owner_id   = Request::input("owner_id");
      $data["slot_position"]   = Request::input("position");
      $data["slot_code"]       = Request::input("slot_no");
      $binary_settings = Tbl_binary_settings::first();
    
      $slot = Tbl_slot::where('slot_id',$owner_id)->first();
      $position = Member::get_strong_leg_position($owner_id);
      if($binary_settings->binary_auto_placement_based_on_direct && $binary_settings->binary_number_of_direct_for_auto_placement > 0 && $position) {
        $last_outer =Tbl_tree_placement::where("placement_parent_id",$owner_id)->where("placement_position", $position)->where("position_type","OUTER")->orderBy('tree_placement_id','desc')->first();
        if($last_outer) {
            $slot2 = Tbl_slot::where('slot_id',$last_outer->placement_child_id)->first();
            $data['slot_placement'] = $slot2->slot_no;
        } else {
            $data['slot_placement'] = $slot->slot_no;
        }
        $data['slot_position'] = $position;
      } else if($binary_settings->binary_extreme_position) {
        $last_outer =Tbl_tree_placement::where("placement_parent_id",$owner_id)->where("placement_position",$data['slot_position'])->where("position_type","OUTER")->orderBy('tree_placement_id','desc')->first();

        if($last_outer) {
            $slot2 = Tbl_slot::where('slot_id',$last_outer->placement_child_id)->first();
            $data['slot_placement'] = $slot2->slot_no;
        } else {
            $data['slot_placement'] = $slot->slot_no;
        }
      } else {
        $data["slot_placement"]  = Request::input("placement");
      }
      $response                = Slot::place_slot($data,"member_downline",$owner_id);
      $placement                 = Tbl_slot::where("slot_no",$data["slot_placement"])->first();
      $new_slot                  = Tbl_slot::where("slot_placement",$placement->slot_id)->where("slot_position",$data['slot_position'])->owner()->select("last_name","first_name","slot_id","slot_no","slot_position","slot_placement")->first();

      if($new_slot) {
        $response["slot_placement"]= $placement->slot_id;
        $response["new_slot"]      = $new_slot;
        $response["placement"]     = $response["new_slot"]->slot_no;
        $response["level"]         = Tbl_tree_placement::where("placement_parent_id",Request::input("root_id"))->where("placement_child_id",$placement->slot_id)->first() ? Tbl_tree_placement::where("placement_parent_id",Request::input("root_id"))->where("placement_child_id",$placement->slot_id)->first()->placement_level + 1 : 1;
      } else  {
        $response["status"]  = "error";
        $response["status_message"][0] = "Slot Not Placed";
      }
      return $response;
    }

}
