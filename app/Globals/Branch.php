<?php
namespace App\Globals;

use DB;
use Carbon\Carbon;

use App\Globals\Audit_trail;
use App\Models\Tbl_branch;
use App\Models\Tbl_cashier;
use App\Models\Tbl_inventory;
use App\Models\Tbl_item;
use App\Models\User;

use App\Models\Tbl_codes;
use Validator;
use Hash;
use Crypt;
use Request;

class Branch
{
	public static function add($data)
	{

		if($data['branch_type'] == 'Branch')
		{
			$rules["branch_name"] = "unique:tbl_branch,branch_name";
			$rules["branch_location"] = "required";
			$rules["branch_type"] = "required";
		}
		else
		{
			$rules["branch_name"] = "unique:tbl_branch,branch_name";
			$rules["branch_location"] = "required";
			$rules["branch_first_name"] = "required";
			$rules["branch_last_name"] = "required";
			$rules["branch_type"] = "required";
			$rules["branch_email"] = "unique:users,email";
			$rules["branch_password"] = "required";
		}
		$validator = Validator::make($data, $rules);

        if ($validator->fails()) 
        {
            $return["status"]         = "error"; 
			$return["status_code"]    = 400; 
			$return["status_message"] = $validator->messages()->all();
        }
        else
        {
			$insert['branch_name']			=	$data['branch_name'];
			$insert['branch_location']		=	$data['branch_location'];
			$insert['branch_type']			=	$data['branch_type'];
			$insert['branch_date_created']	=	Carbon::now();
			if($data['branch_type'] == 'Stockist')
			{
				$insert['stockist_level'] 	= $data['stockist_level'];
			}

			$branch_id = Tbl_branch::insertGetId($insert);
			$new['branch'] = Tbl_branch::where('branch_id',$branch_id)->first();
			if($data['branch_type'] == 'Stockist')
			{
				$insert_stockist_user["email"]				= $data["branch_email"];
				$insert_stockist_user["password"]			= Hash::make($data["branch_password"]);
				$insert_stockist_user["crypt"]				= Crypt::encryptString($data["branch_password"]);
				$insert_stockist_user["created_at"]			= Carbon::now();
				$insert_stockist_user["type"]				= "stockist";
				$insert_stockist_user["first_name"]			= $data["branch_first_name"];
				$insert_stockist_user["last_name"]			= $data["branch_last_name"];
				$insert_stockist_user["contact"]				= $data["branch_contact"];
				$insert_stockist_user["country_id"]	    	= 1;
				$insert_stockist_user["name"]	            = $data["branch_first_name"]." ".$data["branch_last_name"];
				
				$stockist_user_id = User::insertGetId($insert_stockist_user);
				$new['stockist_user'] = User::where('id',$stockist_user_id)->first();
				if(is_numeric($stockist_user_id))
				{
					$insert_stockist['stockist_user_id']		= $stockist_user_id;
					$insert_stockist['stockist_branch_id']		= $branch_id;
					$insert_stockist['stockist_level']			= $data['stockist_level'];

					$stockist_id = DB::table('tbl_stockist')->insertGetId($insert_stockist);
					$new['stockist'] = DB::table('tbl_stockist')->where('stockist_id',$stockist_id)->first();
				}

				$insert_access_list['cashier_access_branch'] = $branch_id;
				$insert_access_list['cashier_type'] 		 = "Stockist";

				$cashier_access_id = DB::table('tbl_cashier_access')->insertGetId($insert_access_list);
				$new['cashier_access'] = DB::table('tbl_cashier_access')->where('cashier_access_id',$cashier_access_id)->first();
				$action   = "Create Add Branch/Stockist";
				$user     = Request::user()->id;
				Audit_trail::audit(null,serialize($new),$user,$action);

				Slot::create_blank_slot($stockist_user_id,0,0,1);
			}

			$item_list = Tbl_item::count();
			
			if($item_list == 0)
			{
				Tbl_inventory::insert(['inventory_branch_id' => $branch_id]);
			}
			else
			{
				$item_list = Tbl_item::get();
				foreach($item_list as $key=>$value)
				{
					$insert_inventory['inventory_branch_id'] = $branch_id;
					$insert_inventory['inventory_item_id'] = $value->item_id;
					Tbl_inventory::insert($insert_inventory);
				}
			}

			if($data['branch_type'] == 'Stockist')
			{
				$return["status"]         = "success"; 
				$return["status_code"]    = 201; 
				$return["status_message"] = "Stockist is successfully added";
			}
			else
			{
				$return["status"]         = "success"; 
				$return["status_code"]    = 201; 
				$return["status_message"] = "Branch is successfully added";
			}			

		}

		return $return;
	}

	
	public static function get($status = null)
	{
		$return = [];
		if($status == 'restore')
		{
			$branch = Tbl_branch::where('archived', 1)->leftJoin('tbl_stockist_level', 'tbl_branch.stockist_level', '=', 'tbl_stockist_level.stockist_level_id')->get();
		}
		else
		{
			$branch = Tbl_branch::where('archived', 0)->leftJoin('tbl_stockist_level', 'tbl_branch.stockist_level', '=', 'tbl_stockist_level.stockist_level_id')->get();
		}
	

		foreach($branch as $key => $value)
		{
			$product_quantity[$key] 		= Tbl_inventory::JoinItem()->where('tbl_inventory.inventory_branch_id', $value->branch_id)->ItemTypeProduct()->sum('inventory_quantity');

			$membership_quantity[$key] 		= Tbl_inventory::JoinItem()->where('tbl_inventory.inventory_branch_id', $value->branch_id)->ItemTypeMembershipkit()->sum('inventory_quantity');

			$cashier_count[$key] 			= Tbl_cashier::where('cashier_branch_id', $value->branch_id)->count();

			$sold_membership_quantity[$key] = Tbl_codes::Inventory()->InventoryItem()->CheckIfSold(1)->CheckIfKit()->where('tbl_inventory.inventory_branch_id', $value->branch_id)->get();

			$sold_product_quantity[$key] 	= Tbl_codes::Inventory()->InventoryItem()->CheckIfSold(1)->CheckIfProduct()->where('tbl_inventory.inventory_branch_id', $value->branch_id)->get();
			
			$sales 					= Tbl_codes::Inventory()->InventoryItem()->where('tbl_inventory.inventory_branch_id', $value->branch_id)->CheckIfSold(1)->sum('item_price');
			$return[$key] = $value->toArray();

			$return[$key] += ['membership_codes_count' => $membership_quantity[$key]];
			$return[$key] += ['product_codes_count' => $product_quantity[$key]];
			$return[$key] += ['cashier_count' => $cashier_count[$key]];
			$return[$key] += ['sold_membership_quantity' => count($sold_membership_quantity[$key])];
			$return[$key] += ['sold_product_quantity' => count($sold_product_quantity[$key])];
			$return[$key] += ['total_sales' => $sales];	
			
		}
		return $return;
	}

	public static function get_data($id)
	{

		$return = Tbl_branch::join('tbl_inventory', 'tbl_branch.branch_id', '=', 'tbl_inventory.inventory_branch_id')->where('tbl_branch.branch_id', $id)->first();	

		if($return->branch_type == 'Stockist')
		{
			$return = Tbl_branch::join('tbl_inventory', 'tbl_branch.branch_id', '=', 'tbl_inventory.inventory_branch_id')->where('tbl_branch.branch_id', $id)->join('tbl_stockist','tbl_stockist.stockist_branch_id','=','tbl_branch.branch_id')->join('users','users.id','=','tbl_stockist.stockist_user_id')->join('tbl_stockist_level','tbl_stockist_level.stockist_level_id','=','tbl_stockist.stockist_level')->first();
			
			$return->pass = Crypt::decryptString($return->crypt);
			return $return;
		}
		else
		{
			return $return;
		}


	}

	public static function archive($id)
	{
		$return = Tbl_branch::where('branch_id', $id)->update(['archived' => 1]);

		return $return;
	}
	public static function restore($id)
	{
		$return = Tbl_branch::where('branch_id', $id)->update(['archived' => 0]);

		return $return;
	}

	public static function edit($data)
	{	
		$update['branch_name']			=	$data['branch_name'];
		$update['branch_location']		=	$data['branch_location'];
		$update['branch_type']			=	$data['branch_type'];
		$update['add_member']			=	$data['add_member'];
		$update['create_slot']			=	$data['create_slot'];
		$update['custom_code']			=	$data['custom_code'];

		Tbl_branch::where('branch_id', $data['branch_id'])->update($update);



		if($data['branch_type'] == 'Stockist')
		{
			$update_stockist_user["email"]				= $data["email"];
			$update_stockist_user["password"]			= Hash::make($data["pass"]);
			$update_stockist_user["crypt"]				= Crypt::encryptString($data["pass"]);
			$update_stockist_user["created_at"]			= Carbon::now();
			$update_stockist_user["type"]				= "stockist";
			$update_stockist_user["first_name"]			= $data["first_name"];
			$update_stockist_user["last_name"]			= $data["last_name"];
			$update_stockist_user["contact"]				= $data["contact"];
			$update_stockist_user["country_id"]	    	= 1;
			$update_stockist_user["name"]	            = $data["first_name"]." ".$data["last_name"];
			
			User::where('id',$data['id'])->update($update_stockist_user);

			$update_stockist['stockist_level']			= $data['stockist_level'];
			
			DB::table('tbl_stockist')->where('stockist_user_id',$data['id'])->update($update_stockist);
			DB::table('tbl_branch')->where('branch_id', $data['branch_id'])->update($update_stockist);
			$return["status"]         = "success"; 
			$return["status_code"]    = 200; 
			$return["status_message"] = "Stockist Successfully Updated!";
			return $return;
		}
		else
		{
			$return["status"]         = "success"; 
			$return["status_code"]    = 200; 
			$return["status_message"] = "Branch Successfully Updated!";
			return $return;
		}
	}

	public static function search($filters = null)
	{
		// dd($filters['branch_type']);
		if($filters['branch_type'] == "Archived")
		{
			$data = Tbl_branch::where("tbl_branch.archived", 1)->leftJoin('tbl_stockist_level', 'tbl_branch.stockist_level', '=', 'tbl_stockist_level.stockist_level_id');
		}
		else
		{
			$data = Tbl_branch::where("tbl_branch.archived", 0)->leftJoin('tbl_stockist_level', 'tbl_branch.stockist_level', '=', 'tbl_stockist_level.stockist_level_id');
		}

		if(isset($filters["branch_type"]) && $filters["branch_type"] != "all")
		{
			if($filters['branch_type'] == "Archived")
			{
				$data = $data->where("tbl_branch.archived",1);
			}
			else
			{
				$data = $data->where("branch_type", $filters["branch_type"]);
			}
			
		}
		if(isset($filters["branch_location"]) && $filters["branch_location"] != "all")
		{
			$data = $data->where("branch_location", $filters["branch_location"]);
		}
		if(isset($filters["search_key"]))
		{
			$data = $data->where("branch_name", "like", "%". $filters["search_key"] . "%");
		}
		
		$data = $data->get();
		$check_data = 0;
		foreach($data as $key => $value)
		{
			$product_quantity[$key] 		= Tbl_inventory::JoinItem()->where('tbl_inventory.inventory_branch_id', $value->branch_id)->ItemTypeProduct()->sum('inventory_quantity');

			$membership_quantity[$key] 		= Tbl_inventory::JoinItem()->where('tbl_inventory.inventory_branch_id', $value->branch_id)->ItemTypeMembershipkit()->sum('inventory_quantity');

			$cashier_count[$key] 			= Tbl_cashier::where('cashier_branch_id', $value->branch_id)->count();

			$sold_membership_quantity[$key] = Tbl_codes::Inventory()->InventoryItem()->CheckIfSold(1)->CheckIfKit()->where('tbl_inventory.inventory_branch_id', $value->branch_id)->get();

			$sold_product_quantity[$key] 	= Tbl_codes::Inventory()->InventoryItem()->CheckIfSold(1)->CheckIfProduct()->where('tbl_inventory.inventory_branch_id', $value->branch_id)->get();
			
			$sales 					= Tbl_codes::Inventory()->InventoryItem()->where('tbl_inventory.inventory_branch_id', $value->branch_id)->CheckIfSold(1)->sum('item_price');
			$return[$key] = $value->toArray();

			$return[$key] += ['membership_codes_count' => $membership_quantity[$key]];
			$return[$key] += ['product_codes_count' => $product_quantity[$key]];
			$return[$key] += ['cashier_count' => $cashier_count[$key]];
			$return[$key] += ['sold_membership_quantity' => count($sold_membership_quantity[$key])];
			$return[$key] += ['sold_product_quantity' => count($sold_product_quantity[$key])];
			$return[$key] += ['total_sales' => $sales];	
			$check_data = 1;
		}
		
		if($check_data == 1)
		{
			return $return;
		}
		else
		{
			$return = "no data";
			return $return;
		}
		
	}

	public static function get_stockist()
	{
		$return = DB::table("tbl_stockist_level")->where('archive', 0)->get();
		
		return $return;
	}

	public static function add_stockist($data)
	{
		foreach($data as $key => $value)
		{
			if($value["stockist_level_name"] == null || $value["stockist_level_name"] == "")
			{
				continue;
			}

			$rules["stockist_level_name"] = "required";

			$validator = Validator::make($value, $rules);

	        if ($validator->fails()) 
	        {
	            $return["status"]         = "error"; 
				$return["status_code"]    = 400; 
				$return["status_message"] = $validator->messages()->all();
	        }
	        else
	        {
	        	$check_exist = DB::table('tbl_stockist_level')->where('archive', 0)->where('stockist_level_name', $value['stockist_level_name'])->first();
	        	$success_add = 0;
	        	if(!$check_exist)
	        	{
	        		$insert['stockist_level_name']			= 	$value['stockist_level_name'];
		        	$insert['stockist_level_date_created'] 	=	Carbon::now();

		        	$stockist_level_id = DB::table('tbl_stockist_level')->insertGetId($insert);

		        	$get_item 		   = Tbl_item::get();
		        	foreach($get_item as $key => $value)
		        	{
		        		$insert_stockist_discount['stockist_level_id'] 		= $stockist_level_id;
		        		$insert_stockist_discount['item_id']				= $value->item_id;
		        		DB::table('tbl_item_stockist_discount')->insert($insert_stockist_discount);
					}
					$new_value = DB::table('tbl_stockist_level')->where('stockist_level_id',$stockist_level_id)->first();

					$action    = "Add Stockist Level"; 
					$user      = Request::user()->id;

					Audit_trail::audit(null,serialize($new_value),$user,$action);
		        	$success_add = $success_add + 1;
		        	$return["status"]         = "success"; 
					$return["status_code"]    = 200; 
					$return["status_message"] = "Stockist level Successfully created.";
	        	}
			}
        }

        if($success_add == 0)
    	{
    		$return["status"]         = "error"; 
			$return["status_code"]    = 400; 
			$return["status_message"] = "Stockist level already exists.";
    	}

        return $return;
	}

	public static function archive_stockist($data)
	{
		DB::table("tbl_stockist_level")->where("stockist_level_name", $data)->update(['archive' => 1]);
	}

	public static function get_access_list($branch_id)
	{
		$return['stockist'] = DB::table('tbl_cashier_access')->where('cashier_access_branch', $branch_id)->where('cashier_type', 'Stockist')->first();
		$return['manager'] = DB::table('tbl_cashier_access')->where('cashier_access_branch', $branch_id)->where('cashier_type', 'Manager')->first();
		$return['cashier'] = DB::table('tbl_cashier_access')->where('cashier_access_branch', $branch_id)->where('cashier_type', 'Cashier')->first();

		return $return;
	}

	public static function access_list_submit($data)
	{
		foreach($data as $key => $value)
		{
			if($key == 'stockist' && $value)
			{
				$update['add_member'] = $value['add_member'];
				$update['create_slot'] = $value['create_slot'];
				$update['overall_discount'] = $value['overall_discount'];
				$old['tbl_cashier_access'] = DB::table('tbl_cashier_access')->where('cashier_access_id', $value['cashier_access_id'])->first();
				DB::table('tbl_cashier_access')->where('cashier_access_id', $value['cashier_access_id'])->update($update);
				$new['tbl_cashier_access'] = DB::table('tbl_cashier_access')->where('cashier_access_id', $value['cashier_access_id'])->first();
				$return["status"]         = "success"; 
				$return["status_code"]    = 200; 
				$return["status_message"] = "Stockist access updated.";
			}
			if($key == 'cashier' && $value)
			{
				$update['add_member'] = $value['add_member'];
				$update['create_slot'] = $value['create_slot'];
				$update['overall_discount'] = $value['overall_discount'];
				$old['tbl_cashier_access'] = DB::table('tbl_cashier_access')->where('cashier_access_id', $value['cashier_access_id'])->first();
				DB::table('tbl_cashier_access')->where('cashier_access_id', $value['cashier_access_id'])->update($update);
				$new['tbl_cashier_access'] = DB::table('tbl_cashier_access')->where('cashier_access_id', $value['cashier_access_id'])->first();
				$return["status"]         = "success"; 
				$return["status_code"]    = 200; 
				$return["status_message"] = "Cashier access updated.";
			}
			if($key == 'manager' && $value)
			{
				$update['add_member'] = $value['add_member'];
				$update['create_slot'] = $value['create_slot'];
				$update['overall_discount'] = $value['overall_discount'];
				$old['tbl_cashier_access'] = DB::table('tbl_cashier_access')->where('cashier_access_id', $value['cashier_access_id'])->first();
				DB::table('tbl_cashier_access')->where('cashier_access_id', $value['cashier_access_id'])->update($update);
				$new['tbl_cashier_access'] = DB::table('tbl_cashier_access')->where('cashier_access_id', $value['cashier_access_id'])->first();
				$return["status"]         = "success"; 
				$return["status_code"]    = 200; 
				$return["status_message"] = "Manager access updated.";
			}
		}
			$action = "Manage Access";
			$user   = Request::user()->id;
			Audit_trail::audit(serialize($old),serialize($new),$user,$action);
		return $return;
	}
}
