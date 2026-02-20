<?php
namespace App\Globals;
use DB;
use Carbon\Carbon;
use App\Globals\Audit_trail;
use App\Globals\Slot;
use App\Models\Tbl_branch;
use App\Models\Tbl_slot;
use App\Models\Tbl_cashier;
use App\Models\Tbl_codes;
use App\Models\Tbl_inventory;
use App\Models\Tbl_item;
use App\Models\Tbl_code_alias;
use App\Models\Tbl_membership;
use App\Models\Tbl_slot_limit;
use App\Models\Rel_item_kit;

use App\Models\Users;
use Validator;
use Request;
class Code
{
	public static function update_inventory($item_id,$quantity, $branch_id = null)
	{
		$rel_item_kit = Rel_item_kit::where('rel_item_kit.item_id',$item_id)->join('tbl_item','tbl_item.item_id','=','rel_item_kit.item_inclusive_id')->get();
		if(count($rel_item_kit) > 0)
		{
			foreach($rel_item_kit as $key=>$value)
			{
				$inventory =  Tbl_inventory::where('inventory_item_id',$value->item_inclusive_id);
				if($branch_id)
				{
					$inventory = $inventory->where('inventory_branch_id', $branch_id);
				}
				$inventory = $inventory->first();
				$new_qty            = $inventory->inventory_quantity - ($quantity * $value->item_qty);
				$update['inventory_quantity'] = $new_qty < 0 ? 0 : $new_qty;

				Tbl_inventory::where('inventory_id',$inventory->inventory_id)->update($update);

				$code_count = $quantity * $value->item_qty;
				for($ctr = 0; $ctr < $code_count; $ctr ++)
				{
					$code = Tbl_codes::where('code_inventory_id', $inventory->inventory_id)->where('code_used',0)->where('code_sold', 0)->where('archived', 0)->first();
					// $update_code['code_used']	= 1;
					// $update_code['code_sold']	= 1;
					$update_code['archived']	= 1;
					$update_code['kit_requirement']	= Rel_item_kit::where('item_inclusive_id', $value->item_id)->first()->item_id;
					$update_code['date_packed']	= Carbon::now();

					Tbl_codes::where('code_id', $code->code_id)->update($update_code);
				}
			}
		}
	}

	public static function generate($branch_id, $item_id,$quantity)
	{
		$check_alias = Tbl_code_alias::first();
		if(!$check_alias)
		{
			$insert_alias["code_alias_name"] = "IEC";
			Tbl_code_alias::insert($insert_alias);

			$alias = "IEC";
		}
		else
		{
			$alias = $check_alias->code_alias_name;
		}

		$count = Tbl_codes::orderBy('code_id', 'desc')->first();
		if($count == null)
		{
			$start = 1;
		}
		else
		{
			$start = $count->code_id+1;
		}
		
		for($ctr = 0; $ctr < $quantity; $ctr ++)
		{
			$code[$ctr]['activation'] = $alias. str_pad($start,8,'0',STR_PAD_LEFT);
			$code[$ctr]['pin'] = str_pad(rand(0, pow(10, 8)-1), 8, '0', STR_PAD_LEFT);
			
			$check_activation = Tbl_codes::where('code_pin', $code[$ctr]['pin'])->first();
			if($check_activation === null)
			{
				$inventory				= Tbl_inventory::where([['inventory_branch_id', $branch_id],['inventory_item_id' , $item_id]])->first();
				$insert['code_inventory_id'] = $inventory->inventory_id;
				$insert['code_activation'] = $code[$ctr]['activation'];
				$insert['code_pin'] = $code[$ctr]['pin'];
				Tbl_codes::insert($insert);
			}
			else
			{
				$ctr--;
			}
			$start++;
		}

		Self::update_inventory($item_id,$quantity, $branch_id);
		return $code;
	}
	public static function get($branch_id, $filter = null, $item_id = null, $paginate = null)
	{
		// dd($item_id);
		// dd(Request::input());
		$return = [];
		$inventory = Tbl_inventory::where([['inventory_branch_id', $branch_id], ['inventory_item_id', $item_id]])->first();
		
		if (!$inventory) 
		{
			$inventory_default["inventory_branch_id"] = $branch_id;
			$inventory_default["inventory_status"] = null;
			$inventory_default["inventory_item_id"] = $item_id;
			$inventory_default["inventory_quantity"] = 0;
			
			Tbl_inventory::insert($inventory_default);
		}
		$data = Tbl_codes::where('code_inventory_id', $inventory->inventory_id);
		$data = $data->join("tbl_inventory", "tbl_codes.code_inventory_id", "=", "tbl_inventory.inventory_id");
		$data = $data->join("tbl_branch", "tbl_inventory.inventory_branch_id", "=", "tbl_branch.branch_id");
		$data = $data->where('tbl_codes.archived', 0);
		if($item_id != null) 
		{
			$data = $data->where("tbl_inventory.inventory_item_id", $item_id);
		}
		if(isset($filter["status"]) && $filter["status"] != "all")
		{
			switch($filter["status"])
			{
				case "Used": 
					$data = $data->where(function($query)
					{
						$query->where([['code_used','=',1],['code_sold','=', 0]])->orWhere([['code_used','=',1],['code_sold','=', 1]]);
					});
					// $data = $data->where([['code_used','=',1],['code_sold','=', 0]])->orWhere([['code_used','=',1],['code_sold','=', 1]]);
				break;
				case "Unused": 
					$data = $data->where(function($query)
					{
						$query->where([['code_used','=',0],['code_sold','=', 0]])->orWhere([['code_used','=',0],['code_sold','=', 1]]);
					});
					// $data = $data->where([['code_used','=',0],['code_sold','=', 0]])->orWhere([['code_used','=',0],['code_sold','=', 1]]);
				break;
				case "Sold":
					$data = $data->where(function($query)
					{
						$query->where([['code_used','=',1],['code_sold','=', 0]])->orWhere([['code_used','=',1],['code_sold','=', 1]]);
					}); 
					// $data = $data->where([['code_used','=',1],['code_sold','=', 0]])->orWhere([['code_used','=',1],['code_sold','=', 1]]);
				break;
				case "Unsold":
					$data = $data->where(function($query)
					{
						$query->where([['code_used','=',1],['code_sold','=', 0]])->orWhere([['code_used','=',0],['code_sold','=', 0]]);
					}); 
					// $data = $data->where([['code_used','=',1],['code_sold','=', 0]])->orWhere([['code_used','=',0],['code_sold','=', 0]]);
				break;
				case "Archived": 
					$data = $data->CheckIfArchived(1);
				break;
				default:
					$data = $data->CheckIfArchived();
			}
		}
		if(isset($filter["search"]) && $filter["search"] != "null")
		{
			$data = $data->where(function($query) use ($filter, $inventory) {
				$query->where('tbl_codes.code_inventory_id', $inventory->inventory_id)
					  ->where(function ($q) use ($filter) {
						  $q->where("code_activation", "like", "%". $filter["search"] . "%")
							->orWhere("code_pin", "like", "%". $filter["search"] . "%");
					  });
			});
		}
		if ($paginate && $paginate != 0) 
		{
			$data = $data->paginate($paginate);
		}
		else
		{
			$data = $data->get();
		}
		foreach($data as $key => $value)
		{
			$data[$key]->code_user 		= Tbl_codes::UsedBy($value->code_used_by)->select('name')->first();
			$data[$key]->code_org_buyer = Tbl_codes::UsedBy($value->org_code_sold_to)->pluck('name')->first() != Tbl_codes::UsedBy($value->code_sold_to)->pluck('name')->first() ? Tbl_codes::UsedBy($value->org_code_sold_to)->pluck('name')->first() : Tbl_codes::UsedBy($value->code_sold_to)->pluck('name')->first();
			$data[$key]->code_buyer 	= Tbl_codes::UsedBy($value->code_sold_to)->select('name')->first();
			$data[$key]->code_archived 	= Tbl_codes::where('code_id',$value->code_id)->value('archived');
		}
		return $data;
	}
	public static function delete($code_id,$archived = 1,$user = null)
	{
		$code 			= Tbl_codes::where('code_id', $code_id)->first();
		$inventory_id 	= $code->code_inventory_id;
		Tbl_codes::where('code_id', $code_id)->update(['archived' => $archived]);
		$code_archived  = Tbl_codes::where('code_id', $code_id)->first();
		$count 			= Tbl_codes::where('code_inventory_id', $inventory_id)->CheckIfArchived()->count();
		// dd($count);
		$update['inventory_quantity']	= $count;
		Tbl_inventory::where('inventory_id', $inventory_id)->update($update);
		if ($user != null) 
		{
			$action = 'Archived Code';
			Audit_trail::audit(serialize($code),serialize($code_archived),$user['id'],$action);
		}
		

		$return["status"]         = "success"; 
		$return["status_code"]    = 200; 
		$return["status_message"] = $archived == 0 ? "Restored Successfully" : "Archived Successfully!";
		return $return;
	}
	public static function check_membership_code_unused($code,$pin)
	{
		$codes = Tbl_codes::where("code_activation",$code)->where("code_pin",$pin)->inventory()->inventoryitem()->CheckIfArchived()->where("item_type","membership_kit")->first();
		if($codes)
		{
			if($codes->code_used == 0)
			{
	            $return  = "unused"; 
			}
			else
			{
	            $return  = "used"; 
			}
		}
		else
		{
            $return  = "not_exist"; 
		}
		return $return;
	}
	public static function get_membership_code_details($code,$pin)
	{
		$codes = Tbl_codes::where("code_activation",$code)->where("code_pin",$pin)->inventory()->inventoryitem()->CheckIfArchived()->where("item_type","membership_kit")->first();
		if($codes)
		{

	        $return  = $codes; 
		}
		else
		{
            $return  = null; 
		}
		return $return;
	}
	public static function use_membership_code($code,$pin,$user_id,$from_admin = null, $slot_id = null, $sponsor)
	{
		$code = Tbl_codes::where("code_activation",$code)->where("code_pin",$pin)->inventory()->inventoryitem()->where('tbl_item.archived', 0)->where("item_type","membership_kit")->first();
		
		$x = 0;
		if($code)
		{
			if($from_admin == null)
			{
				if($code->code_user == 'buyer')
				{
					if($user_id != $code->code_sold_to)
					{
						$return['status']	= 'fail';
						$return['status_error'] = "Only the buyer can use this code.";
						$x = $x + 1;
					}
				}

				$slot_info = Tbl_slot::where('slot_id', $slot_id)->where('membership_inactive', 0)->first();
				if($slot_info)
				{
					$check_restricted = Tbl_membership::where('membership_id', $slot_info->slot_membership)->first();
					if($check_restricted && $check_restricted->restriction == 'membership' || $check_restricted->restriction == 'all')
					{
						$return['status']	= 'fail';

						$return['status_error'] = "You are restricted from using membership codes.";
						$x = $x + 1;
					}
				}
			
				if($code->upgrade_own == 1)
				{
					$check_if_own_sponsor = Tbl_slot::where('slot_id', $sponsor)->where('slot_owner', $user_id)->first();
					if(!$check_if_own_sponsor)
					{
						$x = $x + 1;
						$return['status_error'] = "Only the owner can use this code";
						$return['status']	= 'fail';

					}

					$slot_info = Tbl_slot::where('slot_owner', $user_id)->where('membership_inactive', 0)->first();
					if(!$slot_info)
					{
						$return['status']	= 'fail';

						$return['status_error'] = "You need an activated slot to use an upgrade kit.";
						$x = $x + 1;
					}
				}

				if($code->archived > 0)
				{
					$return['status']	= 'fail';

					$return['status_error'] = "This item has been archived.";
					$x = $x + 1;
				}
			}
		}
		else
		{
			$return["status"]  = "not_exist"; 
		}


		if($x == 0)
		{
			if($code)
			{
				if($code->code_used == 0)
				{
					$return["status"]  = "unused"; 
				}
				else
				{
					$return["status"]  = "used"; 
				}
			}
			else
			{
				$return["status"]  = "not_exist"; 
			}

			if($return["status"] == "unused")
			{
				if($from_admin == 1)
				{
					$update['code_sold_to'] = $user_id;
				}
				$return["code_id"]   = $code->code_id;
				$return['slot_quantity'] = $code->slot_qty;
				$return['upgrade'] = $code->upgrade_own;
				$update["code_used"] = 1;
				$update["code_sold"] = 1;
				$update["code_used_by"] = $user_id;
				$update["code_date_used"] = Carbon::now();
				// $update["code_date_sold"] = Carbon::now();
				Tbl_codes::where("code_id",$code->code_id)->update($update);
			}
		}

		return $return;
	}
	public static function get_membership($code,$pin)
	{
		$code = Tbl_codes::where("code_activation",$code)->where("code_pin",$pin)->inventory()->inventoryitem()->first();	
		return $code->membership_id;
	}
	public static function get_member_codes($user_id, $filter)
	{
		$code_list = Tbl_codes::where('code_sold_to', $user_id)->Inventory()->InventoryItem()->InventoryItemMembership()->where('tbl_item.item_type', 'membership_kit');

		if($filter['membership'] != 'all')
		{
			$code_list->where('tbl_membership.membership_name', $filter['membership']);
		}

		if($filter['status'] != 'all')
		{
			if($filter['status'] == 'Used')
			{
				$filter['status'] = 0;
			}
			else
			{
				$filter['status'] = 1;
			}

			$code_list->where('tbl_codes.code_used', $filter['status']);
		}

		if($filter['search'] != '' || $filter['search'] != null)
		{
			$code_list->where("code_activation", "like", "%". $filter["search"] . "%")->orWhere("code_pin", "like", "%". $filter["search"] . "%");
		}

		$s = $code_list->get();
		
		foreach($s as $key => $value)
		{
			$return[$key]['code_activation'] 		= $value->code_activation;
			$return[$key]['code_pin']				= $value->code_pin;
			$return[$key]['membership_name']		= $value->membership_name;
			$return[$key]['code_used']				= $value->code_used;
			$return[$key]['user']					= Users::where('id', $value->code_used_by)->select('name')->first();
			$return[$key]['code_date_used']			= $value->code_date_used;
			$return[$key]['slot_qty']				= $value->slot_qty;
		}
		
		
		$return  = count($s) == 0 ? $s : $return;
		return $return;
	}
	public static function get_claim_codes($slot_id)
	{
		$claim_codes = DB::table('tbl_receipt')->where('buyer_slot_id', $slot_id)->where('claim_code', '!=', 'none')->get();
		
		foreach ($claim_codes as $key => $value) 
		{
			$return[$key]['claim_code'] 		= $value->claim_code;
			$return[$key]['claimed']			= $value->claimed;
		}

		return $return = $claim_codes == null ? "" : $claim_codes;
	}
	public static function get_random($user)
	{
		$return 	= Tbl_codes::Inventory()->InventoryItem()->CheckIfKit()->FromMainBranch()->CheckIfUsed()->CheckIfSold()->CheckIfArchived()->CheckIfSingleSlot()->first();
		$action     ='Get Random Code';
		Audit_trail::audit(null,serialize($return),$user,$action);
		return $return;
	}
	public static function get_code_membership($user, $membership_id)
	{
		$return 	= Tbl_codes::Inventory()->InventoryItem()->CheckIfKit()->FromMainBranch()->CheckIfUsed()->CheckIfSold()->CheckIfArchived()->CheckIfSingleSlot()->GetSpecificMembership($membership_id)->first();
		$action     ='Get Membership';
		Audit_trail::audit(null,serialize($return),$user,$action);
		return $return;
	}
	public static function load_product_code($user_id,$slot_id,$action,$paginate)
	{
		$code_list = Tbl_codes::where('item_type','product')->where('code_sold_to',$user_id)->inventory()->inventoryitem()->CheckIfArchived();
		if($action=="filter")
		{
			$code_list = $code_list->where('item_id',$key);
		}
		else if($action=="search")
		{
			$code_list = $code_list->Search($key);
		}
		// $count_unused = $code_list->where("code_slot_used",null)->count();
		if($paginate == 0)
		{
			$code_list = $code_list->get();
		}
		else
		{
			$code_list = $code_list->paginate(15);
		}

		$item_list = Tbl_item::where('item_type','product')->where("archived",0)->get();


		$return['item_list'] 	= $item_list;
		$return['code_list'] 	= $code_list;
		$return['total_unused'] = Tbl_codes::where('item_type','product')->where('code_sold_to',$user_id)->inventory()->inventoryitem()->CheckIfArchived()->where("code_used",0)->count();

		return $return;
	}

	public static function load_membership_code($user_id,$slot_id,$paginate)
	{
		$code_list = Tbl_codes::where('code_sold_to', $user_id)->Inventory()->InventoryItem()->InventoryItemMembership()->where('tbl_item.item_type', 'membership_kit')->CheckIfArchived();
		$membership = Request::input('filter');
		$status = Request::input('status');
		if(isset($membership) && $membership != "all")
		{
			$code_list->where('tbl_membership.membership_id', $membership);
		}

		if(Request::input('search'))
		{
			$code_list->Search2(Request::input('search'));
		}
		if(isset($status) && $status != "all")
		{
			$code_list->where('tbl_codes.code_used', $status);
		}
		$return['membership_list'] 	= Tbl_membership::where("archive",0)->get();
		if($paginate == 0)
		{
			$return['code_list'] 		= $code_list->get();
		}
		else
		{
			$return['code_list'] 		= $code_list->paginate(15);
		}
		$return['total_unused'] 	= Tbl_codes::where('code_sold_to', $user_id)->Inventory()->InventoryItem()->InventoryItemMembership()->where('tbl_item.item_type', 'membership_kit')->CheckIfArchived()->where("code_used",0)->count();

		foreach($return['code_list'] as $key => $value)
		{
			if($value->code_used==1)
			{
				$return['code_list'][$key]['name'] = Users::where('id', $value->code_used_by)->value('name');
			}
		}

		return $return;
	}
	public static function load_membership_code_by_cashier($membership, $status, $search, $paginate)
	{
		$code_list = Tbl_codes::Inventory()->InventoryItem()->InventoryItemMembership()->where('tbl_item.item_type', 'membership_kit')->CheckIfArchived();
		// $membership = Request::input('filter');
		// $status = Request::input('status');
		if(isset($membership) && $membership != "all")
		{
			$code_list->where('tbl_membership.membership_id', $membership);
		}
		if(isset($search))
		{
			$code_list->Search2($search);
		}
		if(isset($status) && $status != "all")
		{
			$code_list->where('tbl_codes.code_used', $status);
		}
		$return['membership_list'] 	= Tbl_membership::where("archive",0)->get();
		if($paginate == 0)
		{
			$return['code_list'] 		= $code_list->get();
		}
		else
		{
			$return['code_list'] 		= $code_list->paginate($paginate);
		}
		$return['total_unused'] 	= Tbl_codes::Inventory()->InventoryItem()->InventoryItemMembership()->where('tbl_item.item_type', 'membership_kit')->CheckIfArchived()->where("code_used",0)->count();

		foreach($return['code_list'] as $key => $value)
		{
			if($value->code_used==1)
			{
				$return['code_list'][$key]['name'] = Users::where('id', $value->code_used_by)->value('name');
			}
		}

		return $return;
	}
}