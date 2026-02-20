<?php
namespace App\Globals;

use App\Globals\Audit_trail;
use App\Models\Cart;
use App\Models\Rel_item_kit;
use App\Models\Tbl_address;
use App\Models\Tbl_branch;
use App\Models\Tbl_cashier;
use App\Models\Tbl_cashier_bonus;
use App\Models\Tbl_cashier_bonus_settings;
use App\Models\Tbl_codes;
use App\Models\Tbl_currency;
use App\Models\Tbl_dropshipping_bonus;
use App\Models\Tbl_inventory;
use App\Models\Tbl_island_group;
use App\Models\Tbl_item;
use App\Models\Tbl_item_direct_referral_settings;
use App\Models\Tbl_item_membership_discount;
use App\Models\Tbl_item_points;
use App\Models\Tbl_item_rating;
use App\Models\Tbl_item_stairstep_rank_discount;
use App\Models\Tbl_item_stockist_discount;
use App\Models\Tbl_lalamove;
use App\Models\Tbl_lockdown_autoship_items;
use App\Models\Tbl_membership;
use App\Models\Tbl_mlm_plan;
use App\Models\Tbl_mlm_unilevel_settings;
use App\Models\Tbl_ninja_van;
use App\Models\Tbl_overriding_bonus_settings;
use App\Models\Tbl_product_category;
use App\Models\Tbl_product_downline_discount;
use App\Models\Tbl_product_personal_cashback;
use App\Models\Tbl_product_subcategory;
use App\Models\Tbl_receipt;
use App\Models\Tbl_slot;
use App\Models\Tbl_stairstep_items;
use App\Models\Tbl_stairstep_settings;
use App\Models\Tbl_team_sales_bonus_level;
use App\Models\Tbl_team_sales_bonus_settings;
use App\Models\Tbl_unilevel_items;
use App\Models\Tbl_wallet;
use App\Models\Tbl_retailer_override;

use App\Models\Users;
use Carbon\Carbon;
use DB;
use Request;
use Validator;

class Item
{
    public static function add($data, $user = null)
    {
        $rules["item_sku"] = "required|unique:tbl_item";
        $rules["item_description"] = "required";
        $rules["item_barcode"] = "";
        $rules["item_direct_cashback"] = "required|numeric|min:0";

        $rules["item_price"] = "required|numeric|min:1";
        $rules["item_pv"] = "required|numeric";
        $rules["item_binary_pts"] = "required|numeric";
        $rules["added_days"] = "required|numeric";
        $rules["item_type"] = "required";
        $rules["item_category"] = "required";
        $rules["tag_as"] = "required";

        if ($data["item_type"] == "membership_kit") {
            $rules["membership_id"] = "required";
            $rules["slot_qty"] = "required|numeric|min:1";
            $rules["inclusive_gc"] = "required|numeric|min:0";
        }

        $validator = Validator::make($data, $rules);
        if ($validator->fails()) {
            $return["status"] = "error";
            $return["status_code"] = 400;
            $return["status_message"] = $validator->messages()->all();
        } else {
            $count = Tbl_item::orderBy('item_id', 'desc')->first()->item_id;
            $insert["item_thumbnail"] = $data["item_thumbnail"];
            $insert["item_sku"] = $data["item_sku"];
            $insert["item_description"] = $data["item_description"];
            $insert["item_inclusion_details"] = $data["item_inclusion_details"];
            $insert["item_barcode"] = isset($data["item_barcode"]) ? $data["item_barcode"] : "";
            $insert["item_price"] = $data["item_price"];
            $insert["item_charged"] = $data["item_charged"] ?? 0;
            $insert["qty_charged"] = $data["qty_charged"] ?? 0;
            $insert["item_pv"] = $data["item_pv"];
            $insert["item_vortex_token"] = $data["item_vortex_token"];
            $insert["item_binary_pts"] = $data["item_binary_pts"];
            $insert["added_days"] = $data["added_days"];
            $insert["item_type"] = $data["item_type"];
            $insert["item_category"] = $data["item_category"];
            $insert["item_sub_category"] = $data["item_sub_category"];
            $insert["item_points_incetives"] = $data["item_points_incetives"];
            $insert["item_points_currency"] = $data["item_points_currency"];
            $insert["cashback_points"] = $data["item_cashback_points"] == null || '' ? 0 : $data["item_cashback_points"];
            $insert["cashback_wallet"] = $data["item_cashback_wallet"] == null || '' ? 0 : $data["item_cashback_wallet"];
            $insert["bind_membership_id"] = $data["item_type"] == "product" ? $data["bind_membership_id"] : 0;
            $insert["membership_id"] = $data["item_type"] == "membership_kit" ? $data["membership_id"] : 0;
            $insert["slot_qty"] = $data["item_type"] == "membership_kit" ? $data["slot_qty"] : 0;
            $insert["inclusive_gc"] = $data["item_type"] == "membership_kit" ? $data["inclusive_gc"] : 0;
            $insert["code_user"] = $data['code_user'] != null ? $data['code_user'] : "everyone";
            $insert["upgrade_own"] = $data['upgrade'] != null ? $data['upgrade'] : 0;
            $insert["is_kit_upgrade"] = $data['is_kit_upgrade'] != null ? $data['is_kit_upgrade'] : 0;
            $insert["tag_as"] = $data['tag_as'] ?? null;
            $insert["item_date_created"] = Carbon::now();
            $insert["product_id"] = "P" . str_pad($count + 1, 8, '0', STR_PAD_LEFT);
            $insert['direct_cashback'] = $data['item_direct_cashback'];

            $id = Tbl_item::insertGetId($insert);
            if ($data["item_type"] == "membership_kit") {
                $item_kit = $data["item_kit_fix"];
                if (count($item_kit) > 0) {
                    foreach ($item_kit as $key => $value) {
                        if ($value["item_inclusive_id"] != null && $value["item_qty"] != null) {
                            $insert_kit["item_id"] = $id;
                            $insert_kit["item_inclusive_id"] = $value["item_inclusive_id"];
                            $insert_kit["item_qty"] = $value["item_qty"];
                            Rel_item_kit::insert($insert_kit);
                        }
                    }
                    //audit trail new value item kit
                    $new_value['rel_item_kit'] = Rel_item_kit::where('item_id', $id)->get();
                    //end
                }
            } elseif ($data["item_type"] == "product") {
                $item_membership_discount = $data["item_membership_discount_fix"];
                if (count($item_membership_discount) > 0) {
                    foreach ($item_membership_discount as $key => $value) {
                        $insert_discount["membership_id"] = $value["membership_id"];
                        $insert_discount["item_id"] = $id;
                        // $insert_discount["discount"]      = $value["discount"] < 0 ? 0 : ($value["discount"] > 100 ? 100 : $value["discount"]);
                        $insert_discount["discount"] = $value["discount"];
                        Tbl_item_membership_discount::insert($insert_discount);
                    }
                    //audit trail new value item kit
                    $new_value['item_membership_discount'] = Tbl_item_membership_discount::where('item_id', $id)->get();
                    //end
                }

                // $item_membership_discount = $data["item_membership_discount_fix"];
                // if (count($item_membership_discount) > 0)
                // {
                //     foreach ($item_membership_discount as $key => $value)
                //     {
                //         $insert_discount["membership_id"]     = $value["membership_id"];
                //         $insert_discount["item_id"]           = $id;
                //         $insert_discount["commission"]      = 0;
                //         Tbl_item_membership_discount::insert($insert_discount);
                //     }
                //     //audit trail new value item kit
                //     $new_value['item_membership_discount'] = Tbl_item_membership_discount::where('item_id',$id)->get();
                //     //end
                // }
            }

            //tbl_inventory_item_id
            $check_null_items = Tbl_inventory::where('inventory_item_id', null)->get();

            if (count($check_null_items) == 0) {

                $table_inventory = Tbl_inventory::where('inventory_item_id', '!=', $id)->select('inventory_branch_id')->distinct()->get();
                foreach ($table_inventory as $key => $value) {
                    $insert_inventory['inventory_branch_id'] = $value->inventory_branch_id;
                    $insert_inventory['inventory_item_id'] = $id;
                    Tbl_inventory::insert($insert_inventory);
                }
                //audit trail new value inventory
                $new_value['inventory'] = Tbl_inventory::where('inventory_item_id', $id)->get();
                //end
            } else {
                foreach ($check_null_items as $key => $value) {
                    Tbl_inventory::where('inventory_id', $value->inventory_id)->update(['inventory_item_id' => $id]);
                }
                //audit trail new value inventory
                $new_value['inventory'] = Tbl_inventory::where('inventory_item_id', $id)->get();
                //end
            }

            $table_stockist_discount = Tbl_item_stockist_discount::where('item_id', '!=', $id)->select('stockist_level_id')->distinct()->get();
            foreach ($table_stockist_discount as $key => $value) {
                $insert_stockist_discount['stockist_level_id'] = $value->stockist_level_id;
                $insert_stockist_discount['item_id'] = $id;
                Tbl_item_stockist_discount::insert($insert_stockist_discount);
            }
            //audit trail new value stockist discount
            $new_value['item_stockist_discount'] = Tbl_item_stockist_discount::where('item_id', $id)->get();
            //end
            $table_stairstep_rank_discount = Tbl_item_stairstep_rank_discount::where('item_id', '!=', $id)->select('stairstep_rank_id')->distinct()->get();
            foreach ($table_stairstep_rank_discount as $key => $value) {
                $insert_stairstep_rank_discount['stairstep_rank_id'] = $value->stairstep_rank_id;
                $insert_stairstep_rank_discount['item_id'] = $id;
                Tbl_item_stairstep_rank_discount::insert($insert_stairstep_rank_discount);
            }
            //audit trail new value stairstep rank discount
            $new_value['item_stairstep_rank_discount'] = Tbl_item_stairstep_rank_discount::where('item_id', $id)->get();
            //end
            //audit trail new value
            $new_value['item'] = Tbl_item::where('item_id', $id)->first();
            //end
            if (isset($user)) {
                $action = 'Add Product';
                Audit_trail::audit(null, serialize($new_value), $user['id'], $action);
            }

            $return["status"] = "success";
            $return["status_code"] = 201;
            $return["status_message"] = "Item Created";
            $return["id"] = $id;
        }
        return $return;
    }
    public static function edit($data)
    {
        $rules["item_sku"] = "required";
        $rules["item_description"] = "required";
        $rules["item_barcode"] = "";
        $rules["item_price"] = "required|numeric|min:1";
        // $rules["item_gc_price"]        = "required|numeric|min:1";
        $rules["item_pv"] = "required|numeric";
        $rules["item_vortex_token"] = "required|numeric";
        $rules["item_binary_pts"] = "required|numeric";
        $rules["added_days"] = "required|numeric";
        $rules["item_type"] = "required";
        $rules["item_category"] = "required";
        $rules["tag_as"] = "required";
        $rules["item_category"] = "required";

        $action = 'edit Product';


        if ($data['item']["item_type"] == "membership_kit") {
            $rules["membership_id"] = "required";
            $rules["slot_qty"] = "required|numeric|min:1";
            $rules["inclusive_gc"] = "required|numeric";
        }
        $validator = Validator::make($data['item'], $rules);
        $check_landing_exist = Tbl_item::where('tag_as', 'landing')->first();
        if ($validator->fails()) {
            $return["status"] = "error";
            $return["status_code"] = 400;
            $return["status_message"] = $validator->messages()->all();
        } 
        else if(($check_landing_exist && $check_landing_exist->item_id != $data['item']['item_id']) && $data['item']['tag_as'] == 'landing') {
            $return["status"] = "error";
            $return["status_code"] = 400;
            $return["status_message"][0] = 'Only one product can tag as Landing Page';
        } else {
            $id = $data['item']['item_id'];
            //audit trail old value
            $old_value['item'] = Tbl_item::where("item_id", $id)->first();

            //end
            if ($data['item']["item_thumbnail"]) {
                $update["item_thumbnail"] = $data['item']["item_thumbnail"];
            }
            $update["item_sku"] = $data['item']["item_sku"];
            $update["item_description"] = $data['item']["item_description"];
            $update["item_inclusion_details"] = $data['item']["item_inclusion_details"];
            $update["item_barcode"] = isset($data['item']["item_barcode"]) ? $data['item']["item_barcode"] : "";
            $update["item_price"] = $data['item']["item_price"];
            $update["item_charged"] = $data['item']["item_charged"] ?? 0;
            $update["qty_charged"] = $data['item']["qty_charged"] ?? 0;
            $update["item_gc_price"] = $data['item']["item_gc_price"];
            $update["item_pv"] = $data['item']["item_pv"];
            $update["item_vortex_token"] = $data['item']["item_vortex_token"];
            $update["item_binary_pts"] = $data['item']["item_binary_pts"];
            $update["added_days"] = $data['item']["added_days"];
            $update["item_type"] = $data['item']["item_type"];
            $update["item_category"] = $data['item']["item_category"];
            $update["item_sub_category"] = $data['item']["item_sub_category"];
            $update["item_points_incetives"] = $data['item']["item_points_incetives"];
            $update["item_points_currency"] = $data['item']["item_points_currency"];
            $update["cashback_points"] = $data['item']["item_cashback_points"] == null ? 0 : $data['item']["item_cashback_points"];
            $update["cashback_wallet"] = $data['item']["item_cashback_wallet"] == null ? 0 : $data['item']["item_cashback_wallet"];
            $update["bind_membership_id"] = $data['item']["bind_membership_id"];
            $update["membership_id"] = $data['item']["item_type"] == "membership_kit" ? $data['item']["membership_id"] : 0;
            $update["slot_qty"] = $data['item']["item_type"] == "membership_kit" ? $data['item']["slot_qty"] : 0;
            $update["inclusive_gc"] = $data['item']["item_type"] == "membership_kit" ? $data['item']["inclusive_gc"] : 0;
            $update["code_user"] = $data['item']['code_user'] != null ? $data['item']['code_user'] : "everyone";
            $update["upgrade_own"] = $data['item']['upgrade'] != null ? $data['item']['upgrade'] : 0;
            $update["is_kit_upgrade"] = $data['item']['is_kit_upgrade'] != null ? $data['item']['is_kit_upgrade'] : 0;
            $update["item_availability"] = $data['item']['availability'];
            $update["tag_as"] = $data['item']['tag_as'];
            $update["item_date_created"] = Carbon::now();
            $update['direct_cashback'] = $data['item']['item_direct_cashback'];
            Tbl_item::where("tbl_item.item_id", $id)->update($update);

            // update the value in cart.
            unset($update['item_charged']);
            // $update['discounted_price'] = abs(Tbl_item_membership_discount::where('item_id', $id)->where('membership_id', $update["membership_id"])->pluck('discount')->first() - $update['item_price']);
            // Cart::where('item_id', $id)->update($update);
            Cart::where('item_id', $id)->delete();

            //audit trail new value
            $new_value['item'] = Tbl_item::where("item_id", $id)->first();
            //end
            if ($data['item']["item_type"] == "membership_kit") {
                //audit trail old value item kit
                $old_value['rel_item_kit'] = Rel_item_kit::where('item_id', $id)->get();
                //end
                Rel_item_kit::where("rel_item_kit.item_id", $id)->delete();
                $item_kit = $data['item']["item_kit_fix"];
                if (count($item_kit) > 0) {
                    foreach ($item_kit as $key => $value) {
                        $item_kit_rules["item_qty"] = "required|numeric|min:1";
                        $validator = Validator::make($value, $item_kit_rules);
                        if ($validator->fails()) {
                            $return["status"] = "error";
                            $return["status_code"] = 400;
                            $return["status_message"] = $validator->messages()->all();
                            return $return;
                        } else {
                            if ($value["item_inclusive_id"] != null && $value["item_qty"] != null) {
                                $insert_kit["item_id"] = $id;
                                $insert_kit["item_inclusive_id"] = $value["item_inclusive_id"];
                                $insert_kit["item_qty"] = $value["item_qty"];
                                Rel_item_kit::insert($insert_kit);
                            }
                        }
                    }
                    //audit trail new value item kit
                    $new_value['rel_item_kit'] = Rel_item_kit::where('item_id', $id)->get();
                    //end

                }
                //audit trail old value stairstep rank discount
                $old_value['item_stairstep_rank_discount'] = Tbl_item_stairstep_rank_discount::where('item_id', $id)->get();
                //end
                /*stairstep rank disacount*/
                Tbl_item_stairstep_rank_discount::where("tbl_item_stairstep_rank_discount.item_id", $id)->delete();

                $item_stairstep_rank_discount = $data["stairstep"];

                if (count($item_stairstep_rank_discount) > 0) {
                    foreach ($item_stairstep_rank_discount as $key => $value) {
                        if (isset($value["discount"]) || $value["discount"] == null) {
                            $value["discount"] = 0;
                        }
                        $insert_stairstep_discount["stairstep_rank_id"] = $value["stairstep_rank_id"];
                        $insert_stairstep_discount["item_id"] = $id;
                        $insert_stairstep_discount["discount"] = $value["discount"] < 0 ? 0 : ($value["discount"] > 100 ? 100 : $value["discount"]);

                        Tbl_item_stairstep_rank_discount::insert($insert_stairstep_discount);
                    }
                    //audit trail new value stairstep rank discount
                    $new_value['item_stairstep_rank_discount'] = Tbl_item_stairstep_rank_discount::where('item_id', $id)->get();
                    //end
                }
                //audit trail old value stockist discount
                $old_value['Tbl_item_stockist_discount'] = Tbl_item_stockist_discount::where('item_id', $id)->get();
                //end
                //stockist discount
                Tbl_item_stockist_discount::where("tbl_item_stockist_discount.item_id", $id)->delete();
                $item_stockist_discount = $data["stockist"];
                if (count($item_stockist_discount) > 0) {
                    foreach ($item_stockist_discount as $key => $value) {
                        if (isset($value["discount"]) && $value["discount"] == null) {
                            $value["discount"] = 0;
                        }
                        $insert_stockist_discount["stockist_level_id"] = $value["stockist_level_id"];
                        $insert_stockist_discount["item_id"] = $id;
                        $insert_stockist_discount["discount"] = $value["discount"] < 0 ? 0 : ($value["discount"] > 100 ? 100 : $value["discount"]);
                        Tbl_item_stockist_discount::insert($insert_stockist_discount);
                    }
                    //audit trail new value stockist discount
                    $new_value['Tbl_item_stockist_discount'] = Tbl_item_stockist_discount::where('item_id', $id)->get();
                    //end
                }
                //audit trail old value item kit
                $old_value['item_membership_discount'] = Tbl_item_membership_discount::where('item_id', $id)->get();
                //end
                //membership discounts
                Tbl_item_membership_discount::where("tbl_item_membership_discount.item_id", $id)->delete();
                $item_membership_discount = $data['item']["item_membership_discount_fix"];
                if (count($item_membership_discount) > 0) {
                    foreach ($item_membership_discount as $key => $value) {
                        $insert_discount["membership_id"] = $value["membership_id"];
                        $insert_discount["item_id"] = $id;
                        // $insert_discount["discount"]      = $value["discount"] < 0 ? 0 : ($value["discount"] > 100 ? 100 : $value["discount"]);
                        $insert_discount["discount"] = $value["discount"] ?? 0;
                        Tbl_item_membership_discount::insert($insert_discount);
                    }
                    //audit trail new value item kit
                    $new_value['item_membership_discount'] = Tbl_item_membership_discount::where('item_id', $id)->get();
                    //end
                }
            } elseif ($data['item']["item_type"] == "product") {
                //audit trail old value item kit
                $old_value_membership_discount['item_membership_discount'] = Tbl_item_membership_discount::where('item_id', $id)->get();
                //end
                Tbl_item_membership_discount::where("tbl_item_membership_discount.item_id", $id)->delete();
                $item_membership_discount = $data['item']["item_membership_discount_fix"];
                if (count($item_membership_discount) > 0) {

                    foreach ($item_membership_discount as $key => $value) {
                        $insert_discount["membership_id"] = $value["membership_id"];
                        $insert_discount["item_id"] = $id;
                        // $insert_discount["discount"]      = $value["discount"] < 0 ? 0 : ($value["discount"] > 100 ? 100 : $value["discount"]);
                        $insert_discount["discount"] = $value["discount"] ?? 0;
                        Tbl_item_membership_discount::insert($insert_discount);
                    }
                    //audit trail new value item kit
                    $new_value_membership_discount['item_membership_discount'] = Tbl_item_membership_discount::where('item_id', $id)->get();

                    Audit_trail::audit(serialize($old_value_membership_discount), serialize($new_value_membership_discount), $data['user']['id'], $action);

                    //end
                }

                //audit trail old value item kit
                $old_value_direct_referral['item_direct_referral'] = Tbl_item_direct_referral_settings::where('item_id', $id)->get();
                //end
                Tbl_item_direct_referral_settings::where("tbl_item_direct_referral_settings.item_id", $id)->delete();
                $item_direct_referral = $data['item']["item_direct_referral"];
                if (count($item_direct_referral) > 0) {
                    foreach ($item_direct_referral as $key => $value) {
                        $product_direct["membership_id"] = $value["membership_id"];
                        $product_direct["item_id"] = $id;
                        $product_direct["commission"] = $value['commission'] ?? 0;
                        $product_direct["type"] = $value['type'] ?? null;
                        Tbl_item_direct_referral_settings::insert($product_direct);
                    }
                    $new_value_direct_referral['item_direct_referral'] = Tbl_item_direct_referral_settings::where('item_id', $id)->get();

                    Audit_trail::audit(serialize($old_value_direct_referral), serialize($new_value_direct_referral), $data['user']['id'], $action);

                }

                $old_value_personal_cashback['item_personal_cashback'] = Tbl_product_personal_cashback::where('item_id', $id)->get();
                Tbl_product_personal_cashback::where("tbl_product_personal_cashback.item_id", $id)->delete();
                $item_personal_cashback = $data['item']["item_personal_cashback_fix"];
                if (count($item_personal_cashback) > 0) {
                    foreach ($item_personal_cashback as $key => $value) {
                        $personal_cashback["membership_id"] = $value["membership_id"];
                        $personal_cashback["item_id"] = $id;
                        $personal_cashback["commission"] = $value['commission'] ?? 0;
                        $personal_cashback["type"] = $value['type'] ?? null;
                        Tbl_product_personal_cashback::insert($personal_cashback);
                    }
                    $new_value_personal_cashback['item_personal_cashback'] = Tbl_product_personal_cashback::where('item_id', $id)->get();

                    Audit_trail::audit(serialize($old_value_personal_cashback), serialize($new_value_personal_cashback), $data['user']['id'], $action);

                }

                $old_value_downline_discount['item_downline_discount'] = Tbl_product_downline_discount::where('item_id', $id)->get();
                Tbl_product_downline_discount::where("tbl_product_downline_discount.item_id", $id)->delete();
                $item_downline_discount = $data['item']["item_downline_discount"];
                if (count($item_downline_discount) > 0) {
                    foreach ($item_downline_discount as $key => $value) {
                        $downline_discount["membership_id"] = $value["membership_id"];
                        $downline_discount["item_id"] = $id;
                        $downline_discount["discount"] = $value['discount'] ?? 0;
                        $downline_discount["type"] = $value['type'] ?? null;
                        Tbl_product_downline_discount::insert($downline_discount);
                    }
                    $new_value_downline_discount['item_downline_discount'] = Tbl_product_downline_discount::where('item_id', $id)->get();

                    Audit_trail::audit(serialize($old_value_downline_discount), serialize($new_value_downline_discount), $data['user']['id'], $action);

                }

                //audit trail new value item kit
                $new_value_stockist_discount['item_stockist_discount'] = Tbl_item_stockist_discount::where('item_id', $id)->get();
                //end
                Tbl_item_stockist_discount::where("tbl_item_stockist_discount.item_id", $id)->delete();
                $item_stockist_discount = $data["stockist"];
                if (count($item_stockist_discount) > 0) {
                    $value["discount"] = $value["discount"] ?? 0;

                    foreach ($item_stockist_discount as $key => $value) {
                        $insert_stockist_discount["stockist_level_id"] = $value["stockist_level_id"];
                        $insert_stockist_discount["item_id"] = $id;
                        $insert_stockist_discount["discount"] = $value["discount"] < 0 ? 0 : ($value["discount"] > 100 ? 100 : $value["discount"]);
                        Tbl_item_stockist_discount::insert($insert_stockist_discount);
                    }
                    //audit trail new value item kit
                    $new_value_stockist_discount['item_stockist_discount'] = Tbl_item_stockist_discount::where('item_id', $id)->get();

                    Audit_trail::audit(serialize($new_value_stockist_discount), serialize($new_value_stockist_discount), $data['user']['id'], $action);

                    //end
                }

                $old_value_team_sales['item_team_sales_bonus'] = Tbl_team_sales_bonus_settings::where('item_id', $id)->get();
                Tbl_team_sales_bonus_settings::where("tbl_team_sales_bonus_settings.item_id", $id)->delete();
                $item_team_sales_bonus = $data['item']["item_team_sales_bonus_fix"];
                if (count($item_team_sales_bonus) > 0) {
                    foreach ($item_team_sales_bonus as $key => $value) {
                        $team_sales_bonus["membership_id"] = $value["membership_id"];
                        $team_sales_bonus["item_id"] = $id;
                        $team_sales_bonus["commission"] = $value['commission'] ?? 0;
                        $team_sales_bonus["type"] = $value['type'] ?? null;
                        Tbl_team_sales_bonus_settings::insert($team_sales_bonus);
                    }
                    $new_value_team_sales['item_team_sales_bonus'] = Tbl_team_sales_bonus_settings::where('item_id', $id)->get();

                    Audit_trail::audit(serialize($old_value_team_sales), serialize($new_value_team_sales), $data['user']['id'], $action);

                }

                $old_value_overriding_bonus['item_overriding_bonus'] = Tbl_overriding_bonus_settings::where('item_id', $id)->get();
                Tbl_overriding_bonus_settings::where("tbl_overriding_bonus_settings.item_id", $id)->delete();
                $item_overriding_bonus = $data['item']["item_overriding_bonus_fix"];
                if (count($item_overriding_bonus) > 0) {
                    foreach ($item_overriding_bonus as $key => $value) {
                        $overriding_bonus["membership_id"] = $value["membership_id"];
                        $overriding_bonus["item_id"] = $id;
                        $overriding_bonus["commission"] = $value['commission'] ?? 0;
                        $overriding_bonus["type"] = $value['type'] ?? null;
                        Tbl_overriding_bonus_settings::insert($overriding_bonus);
                    }
                    $new_value_overriding_bonus['item_overriding_bonus'] = Tbl_overriding_bonus_settings::where('item_id', $id)->get();

                    Audit_trail::audit(serialize($old_value_overriding_bonus), serialize($new_value_overriding_bonus), $data['user']['id'], $action);

                }

                /*stairstep rank disacount*/
                Tbl_item_stairstep_rank_discount::where("tbl_item_stairstep_rank_discount.item_id", $id)->delete();
                $item_stairstep_rank_discount = $data["stairstep"];

                //audit trail old value stairstep rank discount
                $old_value_stairsteps['item_stairstep_rank_discount'] = Tbl_item_stairstep_rank_discount::where('item_id', $id)->get();
                //end
                /*stairstep rank disacount*/
                Tbl_item_stairstep_rank_discount::where("tbl_item_stairstep_rank_discount.item_id", $id)->delete();

                $item_stairstep_rank_discount = $data["stairstep"];

                if (count($item_stairstep_rank_discount) > 0) {
                    foreach ($item_stairstep_rank_discount as $key => $value) {
                        if (isset($value["discount"]) && $value["discount"] == null) {
                            $value["discount"] = 0;
                        }
                        $insert_stairstep_discount["stairstep_rank_id"] = $value["stairstep_rank_id"];
                        $insert_stairstep_discount["item_id"] = $id;
                        $insert_stairstep_discount["discount"] = $value["discount"] < 0 ? 0 : ($value["discount"] > 100 ? 100 : $value["discount"]);

                        Tbl_item_stairstep_rank_discount::insert($insert_stairstep_discount);
                    }
                    //audit trail new value stairstep rank discount
                    $new_value_stairsteps['item_stairstep_rank_discount'] = Tbl_item_stairstep_rank_discount::where('item_id', $id)->get();

                    Audit_trail::audit(serialize($old_value_stairsteps), serialize($new_value_stairsteps), $data['user']['id'], $action);

                    //end
                }

                //audit trail old value item kit
                $old_value_item_points['item_points'] = Tbl_item_points::where('item_id', $id)->get();
                //end
                Tbl_item_points::where("tbl_item_points.item_id", $id)->delete();
                $item_points = $data['item']["item_points_fix"];
                if (count($item_points) > 0) {
                    foreach ($item_points as $key => $value) {
                        $insert_points["item_points_key"] = $value["item_points_key"];
                        $insert_points["item_points_personal_pv"] = $value["item_points_personal_pv"];
                        $insert_points["item_points_group_pv"] = $value["item_points_group_pv"];
                        $insert_points["item_id"] = $id;
                        Tbl_item_points::insert($insert_points);
                    }
                    //audit trail old value item kit
                    $new_value_item_points['item_points'] = Tbl_item_points::where('item_id', $id)->get();

                    Audit_trail::audit(serialize($old_value_item_points), serialize($new_value_item_points), $data['user']['id'], $action);

                    //end
                }
                // RETAILER OVERRIDE
                $old_value_retailer['item_retailer_override'] = Tbl_retailer_override::where('item_id', $id)->get();
                Tbl_retailer_override::where("tbl_retailer_override.item_id", $id)->delete();
                $item_retailer_override = $data['item']["item_retailer_override_fix"];
                if (count($item_retailer_override) > 0) {
                    foreach ($item_retailer_override as $key => $value) {
                        $retailer_override["membership_id"] = $value["membership_id"];
                        $retailer_override["item_id"] = $id;
                        $retailer_override["commission"] = $value['commission'] ?? 0;
                        $retailer_override["type"] = $value['type'] ?? null;
                        Tbl_retailer_override::insert($retailer_override);
                    }
                    $new_value_retailer['item_retailer_override'] = Tbl_retailer_override::where('item_id', $id)->get();

                    Audit_trail::audit(serialize($old_value_retailer), serialize($new_value_retailer), $data['user']['id'], $action);
                }

                // DROPSHIPPING BONUS

                $old_value_personal_rebates['item_dropshipping_bonus'] = Tbl_dropshipping_bonus::where('item_id', $id)->get();
                Tbl_dropshipping_bonus::where("tbl_dropshipping_bonus.item_id", $id)->delete();
                $item_personal_rebates = $data['item']["item_dropshipping_bonus_fix"];

                if (count($item_personal_rebates) > 0) {
                    foreach ($item_personal_rebates as $key => $value) {
                        $personal_rebates["membership_id"] = $value["membership_id"];
                        $personal_rebates["item_id"] = $id;
                        $personal_rebates["commission"] = $value['commission'] ?? 0;
                        $personal_rebates["type"] = $value['type'] ?? null;
                        Tbl_dropshipping_bonus::insert($personal_rebates);
                    }
                    $new_value_personal_rebates['item_dropshipping_bonus'] = Tbl_dropshipping_bonus::where('item_id', $id)->get();

                    Audit_trail::audit(serialize($old_value_personal_rebates), serialize($new_value_personal_rebates), $data['user']['id'], $action);

                }
            }
            

            // foreach ($data['item']['shipping_fee'] as $key => $value) {
            //     // dd ($value);

            //     $insertOrupdate_ninja['item_id'] = $id;
            //     $insertOrupdate_ninja['island_group_id'] = $value['id'];
            //     $insertOrupdate_ninja['item_fee'] = $value['ninja_item_fee'] ?? 0;
            //     $insertOrupdate_ninja['qty_fee'] = $value['ninja_qty_fee'] ?? 0;

            //     $insertOrupdate_lalamove['item_id'] = $id;
            //     $insertOrupdate_lalamove['island_group_id'] = $value['id'];
            //     $insertOrupdate_lalamove['item_fee'] = $value['lalamove_item_fee'] ?? 0;
            //     $insertOrupdate_lalamove['qty_fee'] = $value['lalamove_qty_fee'] ?? 0;

            //     $check_ninja = Tbl_ninja_van::where('island_group_id', $value['id'])->where('item_id', $id)->pluck('item_fee')->first();

            //     if (isset($check_ninja)) {
            //         Tbl_ninja_van::where('island_group_id', $value['id'])->where('item_id', $id)->update($insertOrupdate_ninja);
            //     } else {
            //         Tbl_ninja_van::insert($insertOrupdate_ninja);
            //     }

            //     $check_lalamove = Tbl_lalamove::where('island_group_id', $value['id'])->where('item_id', $id)->pluck('item_fee')->first();

            //     if (isset($check_lalamove)) {
            //         Tbl_lalamove::where('island_group_id', $value['id'])->where('item_id', $id)->update($insertOrupdate_lalamove);
            //     } else {
            //         Tbl_lalamove::insert($insertOrupdate_lalamove);
            //     }

                // DB::table('tbl_ninja_van')->updateOrInsert(
                // [
                //     'item_id'                            => $id,
                // ],
                // [
                //     'item_id'                            => $id,
                //     'island_group_id'                    => $value['id'],
                //     'item_fee'                            => $value['ninja_item_fee'],
                //     'qty_fee'                            => $value['ninja_qty_fee'],
                // ]);

                // DB::table('tbl_lalamove')->updateOrInsert(
                // [
                //     'item_id'                            => $id,
                // ],
                // [
                //     'item_id'                            => $id,
                //     'island_group_id'                    => $value['id'],
                //     'item_fee'                            => $value['lalamove_item_fee'],
                //     'qty_fee'                            => $value['lalamove_qty_fee'],
                // ]);
            // }

            $get_plan_status = Tbl_mlm_plan::where('mlm_plan_code', 'TEAM_SALES_BONUS')->first()->mlm_plan_enable;
            if ($get_plan_status == 1) {
                $data["membership_settings"] = Tbl_membership::where('archive', 0)->get();

                foreach ($data["membership_settings"] as $key => $value) {
                    if (isset($data['item']['item_team_sales_bonus_level']["team_sales_bonus_settings"][$value["membership_id"]])) {
                        // dd($data['item']["item_team_sales_bonus_level"]);
                        /* GET THE DATA SETTINGS PER MEMBERSHIP */
                        foreach ($data['item']['item_team_sales_bonus_level']["team_sales_bonus_settings"][$value["membership_id"]] as $membership_id => $per_membership) {
                            $level = 1;
                            /* GET THE DATA SETTINGS PER LEVEL OF TARGET MEMBERSHIP */
                            foreach ($per_membership as $team_sales_bonus) {
                                /* membership_entry_id  = membership_entry_id */
                                /* team_sales_bonus = team_sales_bonus*/
                                $check = Tbl_team_sales_bonus_level::where('item_id', $data['item']['item_id'])->where("membership_level", $level)->where("membership_id", $value["membership_id"])->where("membership_entry_id", $membership_id)->first();
                                if ($check) {
                                    $update_level["membership_level"] = $level;
                                    $update_level["membership_id"] = $value["membership_id"];
                                    $update_level["membership_entry_id"] = $membership_id;
                                    $update_level["team_sales_bonus"] = $team_sales_bonus;
                                    $update_level["item_id"] = $data['item']['item_id'];
                                    Tbl_team_sales_bonus_level::where('item_id', $data['item']['item_id'])->where("membership_level", $level)->where("membership_id", $value["membership_id"])->where("membership_entry_id", $membership_id)->update($update_level);
                                } else {
                                    $insert["membership_level"] = $level;
                                    $insert["membership_id"] = $value["membership_id"];
                                    $insert["membership_entry_id"] = $membership_id;
                                    $insert["team_sales_bonus"] = $team_sales_bonus;
                                    $insert["item_id"] = $data['item']['item_id'];
                                    Tbl_team_sales_bonus_level::where('item_id', $data['item']['item_id'])->insert($insert);
                                }

                                $level++;
                                if ($level > $value["membership_unilevel_level"]) {
                                    Tbl_team_sales_bonus_level::where('item_id', $data['item']['item_id'])->where("membership_level", ">=", $level)->where("membership_id", $value["membership_id"])->where("membership_entry_id", $membership_id)->delete();
                                }
                            }

                        }
                    }
                }
            }

            if (isset($data['user'])) {
                $action = 'edit Product';
                Audit_trail::audit(serialize($old_value), serialize($new_value), $data['user']['id'], $action);
            }

            $return["status"] = "success";
            $return["status_code"] = 201;
            $return["status_message"] = "Item Updated";
        }
        return $return;
    }
    public static function get_product()
    {
        return Tbl_item::where("tbl_item.archived", 0)->where("tbl_item.item_type", "product")->get();
    }
    public static function get_product_unilevel()
    {
        $products = Tbl_item::where("tbl_item.archived", 0)->where("tbl_item.item_type", "product")->get();
        $unilevel_id = Tbl_mlm_unilevel_settings::first()->mlm_unilevel_settings_id;
        foreach ($products as $key => $value) {
            $check = Tbl_unilevel_items::where("item_id", $value->item_id)->first();
            if (!$check) {
                $insert['unilevel_settings_id'] = $unilevel_id;
                $insert['item_id'] = $value->item_id;
                $insert['item_qty'] = 1;
                $insert['included'] = 0;
                Tbl_unilevel_items::insert($insert);
            }
        }
        $return = Tbl_unilevel_items::leftJoin("tbl_item", "tbl_item.item_id", "=", "tbl_unilevel_items.item_id")
            ->select('included', 'item_sku', 'item_qty', 'tbl_unilevel_items.item_id', 'tbl_unilevel_items_id')->get();
        return $return;
    }
    public static function get_product_stairstep()
    {
        $products = Tbl_item::where("tbl_item.archived", 0)->where("tbl_item.item_type", "product")->get();
        $stairstep_id = Tbl_stairstep_settings::first()->stairstep_settings_id;
        foreach ($products as $key => $value) {
            $check = Tbl_stairstep_items::where("item_id", $value->item_id)->first();
            if (!$check) {
                $insert['stairstep_settings_id'] = $stairstep_id;
                $insert['item_id'] = $value->item_id;
                $insert['item_qty'] = 1;
                $insert['included'] = 0;
                Tbl_stairstep_items::insert($insert);
            }
        }
        $return = Tbl_stairstep_items::leftJoin("tbl_item", "tbl_item.item_id", "=", "tbl_stairstep_items.item_id")
            ->select('included', 'item_sku', 'item_qty', 'tbl_stairstep_items.item_id', 'tbl_stairstep_items_id')->get();
        return $return;
    }

    public static function get_ldautoship()
    {
        $products = Tbl_item::where("tbl_item.archived", 0)->where("tbl_item.item_type", "product")->select("item_id")->get();
        foreach ($products as $key => $value) {
            $check = Tbl_lockdown_autoship_items::where("item_id", $value->item_id)->first();
            if (!$check) {
                $insert['item_id'] = $value->item_id;
                $insert['item_qty'] = 1;
                $insert['included'] = 0;
                Tbl_lockdown_autoship_items::insert($insert);
            }
        }
        $return = Tbl_lockdown_autoship_items::leftJoin("tbl_item", "tbl_item.item_id", "=", "tbl_lockdown_autoship_items.item_id")
            ->select('included', 'item_sku', 'item_qty', 'lockdown_autoship_items_id', "tbl_lockdown_autoship_items.item_id")->get();
        return $return;
    }

    public static function save_product_unilevel($data)
    {
        // dd($data);
        foreach ($data as $key => $value) {
            // dd($value);
            $update["item_qty"] = $value['item_qty'];
            $update["included"] = $value['included'];
            Tbl_unilevel_items::where('item_id', $value['item_id'])->update($update);
        }
    }
    public static function save_ldautoship($data)
    {
        foreach ($data as $key => $value) {

            // dd($value);
            $update["item_qty"] = $value['item_qty'];
            $update["included"] = $value['included'];
            Tbl_lockdown_autoship_items::where('item_id', $value['item_id'])->update($update);
        }
    }
    public static function save_product_stairstep($data)
    {
        // dd($data);
        foreach ($data as $key => $value) {
            // dd($value);
            $update["item_qty"] = $value['item_qty'];
            $update["included"] = $value['included'];
            Tbl_stairstep_items::where('item_id', $value['item_id'])->update($update);
        }
    }
    public static function get_item($filters = null, $limit = null, $branch_id = null, $cashier = null)
    {
        $data = DB::table('tbl_item')->leftjoin('tbl_product_category', 'tbl_product_category.id', 'tbl_item.item_category')->leftJoin('tbl_product_subcategory', 'tbl_product_subcategory.id', 'tbl_item.item_sub_category');
        if ($cashier) {
            $data = $data->where('item_availability', 'cashier')->orWhere('item_availability', 'all');
        }

        if (isset($filters["item_type"]) && $filters["item_type"] != "all") {
            if ($filters['item_type'] == "archived") {
                $data = $data->where('archived', 1);
            } else {
                $data = $data->where("item_type", $filters["item_type"]);
            }
        }

        if (isset($filters["item_category"]) && $filters["item_category"] != "all") {
            if ($filters['item_category'] == "item_category") {
                $data = $data->where('archived', 1);
            } else {
                $data = $data->where("item_category", $filters["item_category"]);
            }
        }

        if (isset($filters["item_category"]) && $filters["item_category"] != "all" && isset($filters["item_sub_category"]) && $filters["item_sub_category"] != "all") {
            if ($filters['item_sub_category'] == "item_sub_category") {
                $data = $data->where('archived', 1);
            } else {
                $data = $data->where("item_sub_category", $filters["item_sub_category"]);
            }
        }
        if (isset($filters["search_key"])) {
            $data = $data->where("item_sku", "like", "%" . $filters["search_key"] . "%");
        }

        if (isset($filters["item_type"]) && $filters["item_type"] != "archived") {
            $data = $data->where("archived", 0);
        }

        if ($limit) {
            $data = $data->paginate($limit);
        } else {
            $data = $data->get();
        }
        return $data;
    }
    public static function get_inventory($data)
    {
        $items = Tbl_item::Unarchived()->JoinInventory()->where('tbl_inventory.inventory_branch_id', $data['branch_id'])->get();
        foreach ($items as $key => $value) {
            $items[$key]->used_codes = Tbl_item::Unarchived()->JoinInventory()->JoinCodesInventory()->where('tbl_inventory.inventory_id', $value->inventory_id)->Used()->count();
            $items[$key]->sold_codes = Tbl_item::Unarchived()->JoinInventory()->JoinCodesInventory()->where('tbl_inventory.inventory_id', $value->inventory_id)->Sold()->count();
            $items[$key]->unclaimed = Tbl_receipt::join('tbl_receipt_rel_item', 'tbl_receipt_rel_item.rel_receipt_id', '=', 'tbl_receipt.receipt_id')->join('tbl_item', 'tbl_item.item_id', '=', 'tbl_receipt_rel_item.item_id')->where('retailer', $value->inventory_branch_id)->where('claimed', 0)->where('tbl_item.item_id', $value->item_id)->sum('quantity');
            $items[$key]->claimed = Tbl_receipt::join('tbl_receipt_rel_item', 'tbl_receipt_rel_item.rel_receipt_id', '=', 'tbl_receipt.receipt_id')->join('tbl_item', 'tbl_item.item_id', '=', 'tbl_receipt_rel_item.item_id')->where('retailer', $value->inventory_branch_id)->where('claimed', 1)->where('tbl_item.item_id', $value->item_id)->sum('quantity');

        }

        return $items;
    }

    public static function check_inventory($id)
    {
        $inventory = Tbl_inventory::where('inventory_item_id', $id)->get();
        foreach ($inventory as $key => $value) {
            $inventory_quantity = Tbl_codes::where('code_inventory_id', $value->inventory_id)->where('archived', 0)->where('code_sold', 0)->where('code_used', 0)->count();
            $update['inventory_quantity'] = $inventory_quantity;
            Tbl_inventory::where('inventory_id', $value->inventory_id)->update($update);
        }

        return 1;
    }

    public static function get_item_inventory($item_id)
    {
        $check = Self::check_inventory($item_id);

        $data = Tbl_item::Unarchived()->JoinInventory()->JoinBranch()
            ->where('tbl_inventory.inventory_item_id', $item_id)
            ->get();
        foreach ($data as $key => $value) {
            $data[$key]->used_codes = Tbl_item::Unarchived()->JoinInventory()->JoinCodesInventory()->where('tbl_inventory.inventory_id', $value->inventory_id)->Used()->count();
            $data[$key]->sold_codes = Tbl_item::Unarchived()->JoinInventory()->JoinCodesInventory()->where('tbl_inventory.inventory_id', $value->inventory_id)->Sold()->count();
            $data[$key]->unclaimed = Tbl_receipt::join('tbl_receipt_rel_item', 'tbl_receipt_rel_item.rel_receipt_id', '=', 'tbl_receipt.receipt_id')->join('tbl_item', 'tbl_item.item_id', '=', 'tbl_receipt_rel_item.item_id')->where('retailer', $value->inventory_branch_id)->where('claimed', 0)->where('tbl_item.item_id', $value->item_id)->sum('quantity');
            $data[$key]->claimed = Tbl_receipt::join('tbl_receipt_rel_item', 'tbl_receipt_rel_item.rel_receipt_id', '=', 'tbl_receipt.receipt_id')->join('tbl_item', 'tbl_item.item_id', '=', 'tbl_receipt_rel_item.item_id')->where('retailer', $value->inventory_branch_id)->where('claimed', 1)->where('tbl_item.item_id', $value->item_id)->sum('quantity');
        }

        return $data;
    }
    public static function get_data($id, $slot_id = null)
    {
        $slot_info = Tbl_slot::where('slot_id', $slot_id)->first();
        $membership = $slot_info->slot_membership ?? 0;
        $data = Tbl_item::where("tbl_item.item_id", $id)->first();
        if ($data) {
            $rel_item_kit = Rel_item_kit::select("rel_item_kit.item_id", "tbl_item.item_id", "rel_item_kit.item_inclusive_id", "rel_item_kit.item_qty")->where("rel_item_kit.item_id", $data->item_id)->leftJoin("tbl_item", "tbl_item.item_id", "=", "rel_item_kit.item_id")->get();

            if ($rel_item_kit && count($rel_item_kit) > 0) {
                $data->item_kit = $rel_item_kit;
            }

            $membership_discount = Tbl_item_membership_discount::where("tbl_item_membership_discount.item_id", $data->item_id)->get();
            if ($membership_discount && count($membership_discount) > 0) {
                $data->membership_discount = $membership_discount;
            }

            $item_direct_referral = Tbl_item_direct_referral_settings::where("tbl_item_direct_referral_settings.item_id", $data->item_id)->leftjoin('tbl_membership', 'tbl_membership.membership_id', '=', 'tbl_item_direct_referral_settings.membership_id')->get();
            if ($item_direct_referral && count($item_direct_referral) > 0) {
                $data->item_direct_referral = $item_direct_referral;
            } else {
                $data->item_direct_referral = Tbl_membership::where('archive', 0)->get();
            }

            $item_personal_cashback = Tbl_product_personal_cashback::where("tbl_product_personal_cashback.item_id", $data->item_id)->get();
            if ($item_personal_cashback && count($item_personal_cashback) > 0) {
                $data->item_personal_cashback = $item_personal_cashback;
            } else {
                $data->item_personal_cashback = Tbl_membership::where('archive', 0)->get();
            }
            $item_downline_discount = Tbl_product_downline_discount::where("tbl_product_downline_discount.item_id", $data->item_id)->leftjoin('tbl_membership', 'tbl_membership.membership_id', '=', 'tbl_product_downline_discount.membership_id')->get();
            if ($item_downline_discount && count($item_downline_discount) > 0) {
                $data->item_downline_discount = $item_downline_discount;
            } else {
                $data->item_downline_discount = Tbl_membership::where('archive', 0)->get();
            }
            $item_points = Tbl_item_points::where("tbl_item_points.item_id", $data->item_id)->get();
            if ($item_points && count($item_points) > 0) {
                $data->item_points = $item_points;
            }

            $check_stockist_list = DB::table('tbl_stockist_level')->where('archive', 0)->join('tbl_item_stockist_discount', 'tbl_stockist_level.stockist_level_id', '=', 'tbl_item_stockist_discount.stockist_level_id')->where('tbl_item_stockist_discount.item_id', $id)->get();

            if (count($check_stockist_list) == 0) {
                $data->stockist_list = DB::table('tbl_stockist_level')->where('archive', 0)->get();
            } else {
                $data->stockist_list = $check_stockist_list;
            }

            $check_rank_list = DB::table('tbl_stairstep_rank')->where('archive', 0)->join('tbl_item_stairstep_rank_discount', 'tbl_item_stairstep_rank_discount.stairstep_rank_id', '=', 'tbl_stairstep_rank.stairstep_rank_id')->where('tbl_item_stairstep_rank_discount.item_id', $id)->get();

            if (count($check_rank_list) == 0) {
                $data->rank_list = DB::table('tbl_stairstep_rank')->where('archive', 0)->get();
            } else {
                $data->rank_list = $check_rank_list;
            }

            $item_team_sales_bonus = Tbl_team_sales_bonus_settings::where("tbl_team_sales_bonus_settings.item_id", $data->item_id)->get();
            if ($item_team_sales_bonus && count($item_team_sales_bonus) > 0) {
                $data->item_team_sales_bonus = $item_team_sales_bonus;
            } else {
                $data->item_team_sales_bonus = Tbl_membership::where('archive', 0)->get();
            }

            $item_overriding_bonus = Tbl_overriding_bonus_settings::where("tbl_overriding_bonus_settings.item_id", $data->item_id)->get();
            if ($item_overriding_bonus && count($item_overriding_bonus) > 0) {
                $data->item_overriding_bonus = $item_overriding_bonus;
            } else {
                $data->item_overriding_bonus = Tbl_membership::where('archive', 0)->get();
            }

            $item_retailer_override = Tbl_retailer_override::where("item_id", $data->item_id)->get();
            if ($item_retailer_override && count($item_retailer_override) > 0) {
                $data->item_retailer_override = $item_retailer_override;
            } else {
                $data->item_retailer_override = Tbl_membership::where('archive', 0)->get();
            }

            $item_dropshipping_bonus = Tbl_dropshipping_bonus::where("item_id", $data->item_id)->get();
            if ($item_dropshipping_bonus && count($item_dropshipping_bonus) > 0) {
                $data->item_dropshipping_bonus = $item_dropshipping_bonus;
            } else {
                $data->item_dropshipping_bonus = Tbl_membership::where('archive', 0)->get();
            }

            $data->direct_cashback_membership = abs((Tbl_membership::where('membership_id', $membership)->pluck('direct_cashback')->first() / 100) * $data->direct_cashback);

            // $data->discounted_price = abs((Tbl_item_membership_discount::where('item_id',$data->item_id)->where('membership_id',$membership)->pluck('discount')->first() /100 )* $data->item_price - $data->item_price);
            $data->discounted_price = abs(Tbl_item_membership_discount::where('item_id', $data->item_id)->where('membership_id', $membership)->pluck('discount')->first() - $data->item_price);

            $get_plan_status = Tbl_mlm_plan::where('mlm_plan_code', 'PRODUCT_DOWNLINE_DISCOUNT')->first()->mlm_plan_enable;
            if ($get_plan_status == 1) {
                if ($slot_info) {
                    if ($slot_info->slot_sponsor > 0) {
                        $get_item_discount = Tbl_slot::where('slot_id', $slot_info->slot_sponsor)
                            ->leftjoin('tbl_membership', 'tbl_membership.membership_id', 'tbl_slot.slot_membership')
                            ->leftjoin('tbl_product_downline_discount', 'tbl_product_downline_discount.membership_id', 'tbl_slot.slot_membership')->where('item_id', $id)
                            ->select('tbl_product_downline_discount.membership_id', 'item_id', 'discount', 'type')->first();

                        if ($get_item_discount) {
                            if ($get_item_discount->type == 'percentage') {
                                $data->discounted_price = abs(($get_item_discount->discount / 100) * $data->item_price - $data->discounted_price);
                                $data->product_downline_discount = ($get_item_discount->discount / 100) * $data->item_price;
                            } else {
                                $data->discounted_price = $data->discounted_price - $get_item_discount->discount;
                                $data->product_downline_discount = $get_item_discount->discount;

                            }
                        }
                    }
                }
            }
            $data->item_ratings = Self::get_ratings($id);

            $shipping_fee = Tbl_island_group::get();

            foreach ($shipping_fee as $key => $value) {

                $shipping_fee[$key]['ninja_item_fee'] = Tbl_ninja_van::where('island_group_id', $value['id'])->where('item_id', $id)->pluck('item_fee')->first();
                $shipping_fee[$key]['ninja_qty_fee'] = Tbl_ninja_van::where('island_group_id', $value['id'])->where('item_id', $id)->pluck('qty_fee')->first();
                $shipping_fee[$key]['lalamove_item_fee'] = Tbl_lalamove::where('island_group_id', $value['id'])->where('item_id', $id)->pluck('item_fee')->first();
                $shipping_fee[$key]['lalamove_qty_fee'] = Tbl_lalamove::where('island_group_id', $value['id'])->where('item_id', $id)->pluck('qty_fee')->first();
            }
            $data->shipping_fee = $shipping_fee;
        }
        return $data;
    }
    public static function archive($id, $user)
    {
        //audit trail old value
        $old_value = Tbl_item::where("tbl_item.item_id", $id)->first();
        //end
        Tbl_item::where("tbl_item.item_id", $id)->update(["archived" => 1]);
        //audit trail new value
        $new_value = Tbl_item::where("tbl_item.item_id", $id)->first();
        //end
        $action = 'Archived Item';
        Audit_trail::audit(serialize($old_value), serialize($new_value), $user['id'], $action);

        $return["status"] = "success";
        $return["status_code"] = 200;
        $return["status_message"] = "Item Archived";
        return $return;
    }

    public static function unarchive($id, $user)
    {
        //audit trail old value
        $old_value = Tbl_item::where("tbl_item.item_id", $id)->first();
        //end
        Tbl_item::where("tbl_item.item_id", $id)->update(["archived" => 0]);
        //audit trail new value
        $new_value = Tbl_item::where("tbl_item.item_id", $id)->first();
        //end
        $action = 'Archived Item';
        Audit_trail::audit(serialize($old_value), serialize($new_value), $user['id'], $action);

        $return["status"] = "success";
        $return["status_code"] = 200;
        $return["status_message"] = "Item Restored";
        return $return;
    }

    public static function restock($data)
    {
        $update['inventory_quantity'] = $data['quantity'];
        $query = Tbl_inventory::where([['inventory_branch_id', '=', $data['branch_id']], ['inventory_item_id', '=', $data['item_id']]])->first();

        if ($query->inventory_quantity == null) {
            Tbl_inventory::where([['inventory_branch_id', '=', $data['branch_id']], ['inventory_item_id', '=', $data['item_id']]])->update($update);
            $return["status"] = "success";
            $return["status_code"] = 200;
            $return["status_message"] = "Item Archived";
            return $return;
        } else {
            $update['inventory_quantity'] = $query->inventory_quantity + $data['quantity'];
            Tbl_inventory::where([['inventory_branch_id', '=', $data['branch_id']], ['inventory_item_id', '=', $data['item_id']]])->update($update);
            $return["branch_id"] = $data['branch_id'];
            $return["status"] = "success";
            $return["status_code"] = 200;
            $return["status_message"] = "Item Archived";
            return $return;
        }
    }
    public static function update_inventory($branch_id, $item_id, $quantity)
    {
        $current_quantity = Tbl_inventory::where([['inventory_branch_id', $branch_id], ['inventory_item_id', $item_id]])->sum('inventory_quantity');

        $update_quantity = $current_quantity + $quantity;
        Tbl_inventory::where([['inventory_branch_id', $branch_id], ['inventory_item_id', $item_id]])->update(['inventory_quantity' => $update_quantity]);
        $return["status"] = "success";
        $return["status_code"] = 200;
        $return["status_message"] = "Updated Successfully!";

        return $return;
    }
    public static function get_all_products($slot_id = null, $filter = null)
    {
        $slot_info = Tbl_slot::where('slot_id', $slot_id)->first();
        $membership = $slot_info->slot_membership ?? 0;
        $get_plan_status = Tbl_mlm_plan::where('mlm_plan_code', 'PRODUCT_DOWNLINE_DISCOUNT')->first()->mlm_plan_enable;
        if ($get_plan_status == 1) {
            if ($slot_info->slot_sponsor > 0) {
                $get_item_discount = Tbl_slot::where('slot_id', $slot_info->slot_sponsor)
                    ->leftjoin('tbl_membership', 'tbl_membership.membership_id', 'tbl_slot.slot_membership')
                    ->leftjoin('tbl_product_downline_discount', 'tbl_product_downline_discount.membership_id', 'tbl_slot.slot_membership')
                    ->select('tbl_product_downline_discount.membership_id', 'item_id', 'discount', 'type')->get();
            } else {
                $get_item_discount = null;
            }
        }
        if ($filter['item_type'] == "product") {
            if($membership) {
                $return = Tbl_item::where('archived', 0)->where('item_type', 'product')->where('item_availability', '!=', 'cashier')
                ->where(function ($query) use ($membership) {
                    $query->where('bind_membership_id', $membership)
                        ->orwhere('bind_membership_id', 0)
                        ->orwhere('bind_membership_id', -1);
                })->join('tbl_inventory', 'tbl_item.item_id', '=', 'tbl_inventory.inventory_item_id')->where('inventory_quantity', '!=', 0);
            } else {
                $return = Tbl_item::where('archived', 0)->where('item_type', 'product')->where('item_availability', '!=', 'cashier')
                ->where(function ($query) use ($membership) {
                    $query->where('bind_membership_id', $membership)
                        ->orwhere('bind_membership_id', 0);
                })->join('tbl_inventory', 'tbl_item.item_id', '=', 'tbl_inventory.inventory_item_id')->where('inventory_quantity', '!=', 0);
            }
            if (isset($filter["branch"])) {
                $return = $return->where("tbl_inventory.inventory_branch_id", $filter["branch"]);
            }
            if (isset($filter["search"])) {
                $return = $return->where("item_sku", "like", "%" . $filter["search"] . "%");
            }
            if (isset($filter["item_category"])) {
                if ($filter["item_category"] != 'all') {
                    $return = $return->where("item_category", $filter["item_category"]);
                }
            }
            if (isset($filter["item_sub_category"])) {
                if ($filter["item_sub_category"] != 'all') {
                    $return = $return->where("item_sub_category", $filter["item_sub_category"]);
                }
            }
            // if($check_product_sharelink != 0)
            // {
            //     $return = $return->where("item_id",$check_product_sharelink);
            // }
            $return = $return->paginate(8);
            foreach ($return as $key => $value) {
                $value['discounted_price'] = abs(Tbl_item_membership_discount::where('item_id', $value->item_id)->where('membership_id', $membership)->pluck('discount')->first() - $value->item_price);
                if ($get_plan_status == 1) {
                    if ($get_item_discount) {
                        foreach ($get_item_discount as $key1 => $discount) {
                            if ($value->item_id == $discount->item_id) {
                                if ($discount->type == 'percentage') {
                                    $value['discounted_price'] = abs(($discount->discount / 100) * $value->item_price - $value->discounted_price);
                                    $value['product_downline_discount'] = ($discount->discount / 100) * $value->item_price;
                                } else {
                                    $value['discounted_price'] = $value->discounted_price - $discount->discount;
                                    $value['product_downline_discount'] = $discount->discount;

                                }
                            }
                        }
                    }
                }
            }
        } else {
            if($membership) {
                $return = Tbl_item::where('archived', 0)->where('item_type', 'membership_kit')->where('item_availability', '!=', 'cashier')
                ->where(function ($query) use ($membership) {
                    $query->where('bind_membership_id', $membership)
                        ->orwhere('bind_membership_id', 0)
                        ->orwhere('bind_membership_id', -1);
                })->join('tbl_inventory', 'tbl_item.item_id', '=', 'tbl_inventory.inventory_item_id')->where('inventory_quantity', '!=', 0);
            } else {
                $return = Tbl_item::where('archived', 0)->where('item_type', 'membership_kit')->where('item_availability', '!=', 'cashier')
                ->where(function ($query) use ($membership) {
                    $query->where('bind_membership_id', $membership)
                        ->orwhere('bind_membership_id', 0);
                })->join('tbl_inventory', 'tbl_item.item_id', '=', 'tbl_inventory.inventory_item_id')->where('inventory_quantity', '!=', 0);
            }
            if (isset($filter["branch"])) {
                $return = $return->where("tbl_inventory.inventory_branch_id", $filter["branch"]);
            }
            if (isset($filter["search"])) {
                $return = $return->where("item_sku", "like", "%" . $filter["search"] . "%");
            }
            if (isset($filter["item_category"])) {
                if ($filter["item_category"] != 'all') {
                    $return = $return->where("item_category", "like", "%" . $filter["item_category"] . "%");
                }
            }
            if (isset($filter["item_sub_category"])) {
                if ($filter["item_sub_category"] != 'all') {
                    $return = $return->where("item_sub_category", "like", "%" . $filter["item_sub_category"] . "%");
                }
            }
            $return = $return->paginate(8);
            foreach ($return as $key => $value) {
                // $value['discounted_price'] = abs((Tbl_item_membership_discount::where('item_id',$value->item_id)->where('membership_id',$membership)->pluck('discount')->first() /100 )* $value->item_price - $value->item_price);
                $value['discounted_price'] = abs(Tbl_item_membership_discount::where('item_id', $value->item_id)->where('membership_id', $membership)->pluck('discount')->first() - $value->item_price);
                if ($get_plan_status == 1) {
                    if ($get_item_discount) {
                        foreach ($get_item_discount as $key1 => $discount) {
                            if ($value->item_id == $discount->item_id) {
                                if ($discount->type == 'percentage') {
                                    $value['discounted_price'] = abs(($discount->discount / 100) * $value->item_price - $value->discounted_price);
                                    $value['product_downline_discount'] = ($discount->discount / 100) * $value->item_price;
                                } else {
                                    $value['discounted_price'] = $value->discounted_price - $discount->discount;
                                    $value['product_downline_discount'] = $discount->discount;

                                }
                            }
                        }
                    }
                }
            }
        }

        return $return;
    }
    public static function get_membership_kit()
    {
        $return = Tbl_item::where('archived', 0)->where('item_type', 'membership_kit')->get();

        return $return;
    }

    public static function get_cart($data, $branch_id = null)
    {
        $slot_info = Tbl_slot::where('slot_id', $data['slot_id'])->first();
        $membership = $slot_info->slot_membership ?? 0;
        $get_plan_status = Tbl_mlm_plan::where('mlm_plan_code', 'PRODUCT_DOWNLINE_DISCOUNT')->first()->mlm_plan_enable;
        $get_item_discount = null;

        $get_address = Tbl_address::where('user_id', Request::user()->id)->where('is_default', 1)->first();

        if ($get_address) {
            if ($get_address->regCode == 13) {
                $island_group = 1;
            } else {
                $island_group = $get_address->island_group;
            }
        }
        if ($get_plan_status == 1) {
            if ($slot_info->slot_sponsor > 0) {
                $get_item_discount = Tbl_slot::where('slot_id', $slot_info->slot_sponsor)
                    ->leftjoin('tbl_membership', 'tbl_membership.membership_id', 'tbl_slot.slot_membership')
                    ->leftjoin('tbl_product_downline_discount', 'tbl_product_downline_discount.membership_id', 'tbl_slot.slot_membership')
                    ->select('tbl_product_downline_discount.membership_id', 'item_id', 'discount', 'type')->get();
            }
        }
        $return = [];
        $test = collect($data['items']);
        $unique = $test->unique()->values()->all();

        foreach ($unique as $key => $value) {
            if ($branch_id == null) {
                $branch_id = Tbl_branch::where('archived', 0)->pluck('branch_id')->first();
                $return[$key] = Tbl_item::where('tbl_item.archived', 0)->join('tbl_inventory', 'tbl_item.item_id', '=', 'tbl_inventory.inventory_item_id')->where('item_id', $value)->where('tbl_inventory.inventory_branch_id', $branch_id)
                    ->leftjoin('tbl_branch', 'tbl_branch.branch_id', 'tbl_inventory.inventory_branch_id')
                    ->first();
            } else {
                $return[$key] = Tbl_item::where('tbl_item.archived', 0)->join('tbl_inventory', 'tbl_item.item_id', '=', 'tbl_inventory.inventory_item_id')->where('item_id', $value)->where('tbl_inventory.inventory_branch_id', $branch_id)
                    ->leftjoin('tbl_branch', 'tbl_branch.branch_id', 'tbl_inventory.inventory_branch_id')
                    ->first();
            }

            $return[$key]->item_qty = 1;

        }
        /*CHECKING DUPLICATE*/
        $item_id = array();
        foreach ($return as $key => $value) {
            if (!in_array($value['item_id'], $item_id)) {
                $item_id[] = [$value['item_id'] => $value['item_qty']];
            } else {
                unset($return[$key]);
            }
        }
        foreach ($return as $key => $value) {
            // $value['discounted_price']                             = abs((Tbl_item_membership_discount::where('item_id',$value->item_id)->where('membership_id',$membership)->pluck('discount')->first() /100 )* $value->item_price - $value->item_price);
            // $value['direct_cashback_membership']                 = abs((Tbl_membership::where('membership_id',$membership)->pluck('direct_cashback')->first() / 100) * $value->direct_cashback);
            // $value['shipping_fee_lalamove']                     = Tbl_lalamove::where('island_group_id',$island_group)->where('item_id',$value->item_id)->first();
            // $value['shipping_fee_ninja']                         = Tbl_ninja_van::where('island_group_id',$island_group)->where('item_id',$value->item_id)->first();

            // $value['discounted_price']                             = abs((Tbl_item_membership_discount::where('item_id',$value->item_id)->where('membership_id',$membership)->pluck('discount')->first() /100 )* $value->item_price - $value->item_price);
            $value['discounted_price'] = abs(Tbl_item_membership_discount::where('item_id', $value->item_id)->where('membership_id', $membership)->pluck('discount')->first() - $value->item_price);
            $value['direct_cashback_membership'] = abs((Tbl_membership::where('membership_id', $membership)->pluck('direct_cashback')->first() / 100) * $value->direct_cashback);
            // $value['shipping_fee_lalamove']                     = Tbl_lalamove::where('island_group_id',$island_group)->where('item_id',$value->item_id)->pluck('item_fee')->first();
            // $value['org_shipping_fee_lalamove']                 = Tbl_lalamove::where('island_group_id',$island_group)->where('item_id',$value->item_id)->pluck('item_fee')->first();
            // $value['shipping_fee_ninja']                         = Tbl_ninja_van::where('island_group_id',$island_group)->where('item_id',$value->item_id)->pluck('item_fee')->first();
            // $value['org_shipping_fee_ninja']                     = Tbl_ninja_van::where('island_group_id',$island_group)->where('item_id',$value->item_id)->pluck('item_fee')->first();
            // $value['qty_fee_lalamove']                            = Tbl_lalamove::where('island_group_id',$island_group)->where('item_id',$value->item_id)->pluck('qty_fee')->first();
            // $value['qty_fee_ninja_van']                            = Tbl_ninja_van::where('island_group_id',$island_group)->where('item_id',$value->item_id)->pluck('qty_fee')->first();

            $value['shipping_fee_lalamove'] = 0;
            $value['org_shipping_fee_lalamove'] = 0;
            $value['shipping_fee_ninja'] = 0;
            $value['org_shipping_fee_ninja'] = 0;
            $value['qty_fee_lalamove'] = 0;
            $value['qty_fee_ninja_van'] = 0;

            if ($get_plan_status == 1) {
                if ($get_item_discount) {
                    foreach ($get_item_discount as $key1 => $discount) {
                        if ($value->item_id == $discount->item_id) {
                            if ($discount->type == 'percentage') {
                                $value['discounted_price'] = abs(($discount->discount / 100) * $value->item_price - $value->discounted_price);
                                $value['product_downline_discount'] = ($discount->discount / 100) * $value->item_price;
                            } else {
                                $value['discounted_price'] = $value->discounted_price - $discount->discount;
                                $value['product_downline_discount'] = $discount->discount;

                            }
                        }
                    }
                }
            }
        }

        return $return;
    }

    public static function get_landing_cart($data, $branch_id = null)
    {
        $test = collect($data['items']);
        $return = [];
        if($test) {
            $unique = $test->unique()->values()->all();

            foreach ($unique as $key => $value) {
                if ($branch_id == null) {
                    $branch_id = Tbl_branch::where('archived', 0)->pluck('branch_id')->first();
                    $return[$key] = Tbl_item::where('tbl_item.archived', 0)->join('tbl_inventory', 'tbl_item.item_id', '=', 'tbl_inventory.inventory_item_id')->where('item_id', $value)->where('tbl_inventory.inventory_branch_id', $branch_id)
                        ->leftjoin('tbl_branch', 'tbl_branch.branch_id', 'tbl_inventory.inventory_branch_id')
                        ->first();
                } else {
                    $return[$key] = Tbl_item::where('tbl_item.archived', 0)->join('tbl_inventory', 'tbl_item.item_id', '=', 'tbl_inventory.inventory_item_id')->where('item_id', $value)->where('tbl_inventory.inventory_branch_id', $branch_id)
                        ->leftjoin('tbl_branch', 'tbl_branch.branch_id', 'tbl_inventory.inventory_branch_id')
                        ->first();
                }
    
                $return[$key]->item_qty = 1;
    
            }
            /*CHECKING DUPLICATE*/
            $item_id = array();
            foreach ($return as $key => $value) {
                if (!in_array($value['item_id'], $item_id)) {
                    $item_id[] = [$value['item_id'] => $value['item_qty']];
                } else {
                    unset($return[$key]);
                }
            }
            foreach ($return as $key => $value) {
                
                $value['shipping_fee_lalamove'] = 0;
                $value['org_shipping_fee_lalamove'] = 0;
                $value['shipping_fee_ninja'] = 0;
                $value['org_shipping_fee_ninja'] = 0;
                $value['qty_fee_lalamove'] = 0;
                $value['qty_fee_ninja_van'] = 0;
            }
    
        }
        return $return;
       
    }

    public static function cashier_sale($payment, $item, $slot, $picked_up, $vat = 0, $manager_discount = 0, $remarks = null)
    {
        $cash_payment = 0;
        $cheque_payment = 0;
        $gc_payment = 0;
        $wallet_payment = 0;
        $payable = 0;
        $subtotal = 0;
        $grand_total = 0;
        $buying_currency = Tbl_currency::where('currency_buying', 1)->select('currency_id')->first();
        //payment is $requested
        if ($payment) {
            if ($payment[0]['method'] == 'cash') {
                $cash_payment = $cash_payment + $payment[0]['amount'];
                $payment_method = DB::table('tbl_cashier_payment_method')->where('cashier_payment_method_name', 'Cash')->first();
            } elseif ($payment[0]['method'] == 'cheque') {
                $cheque_payment = $cheque_payment + $payment[0]['amount'];
                $payment_method = DB::table('tbl_cashier_payment_method')->where('cashier_payment_method_name', 'Cheque')->first();

            } elseif ($payment[0]['method'] == 'gc') {
                $gc_payment = $gc_payment + $payment[0]['amount'];
                $payment_method = DB::table('tbl_cashier_payment_method')->where('cashier_payment_method_name', 'GC')->first();
            } else {
                $wallet_payment = $wallet_payment + $payment[0]['amount'];
                $payment_method = DB::table('tbl_cashier_payment_method')->where('cashier_payment_method_name', 'Wallet')->first();
            }

            //ordered items = $order
            foreach ($item as $key => $value) {
                $order[$key] = Tbl_item::where('item_id', $value['item_id'])->first();
                $order_item[$key]['item_id'] = $value['item_id'];
                $order[$key]->quantity = $value['item_qty'];
                $order_item[$key]['quantity'] = $value['item_qty'];

                $grand_total += $value['discounted_price'] * $value['item_qty'];
            }
            //customer is $customer
            $customer = Tbl_slot::where('slot_no', '=', $slot['slot_no'])->first();
            // checking payments
            $gc_currency_id = Tbl_currency::where('currency_name', 'Gift Card')->select('currency_id')->first();
            $gc_owned = Tbl_wallet::where([['slot_id', '=', $customer->slot_id], ['currency_id', '=', $gc_currency_id->currency_id]])->first();
            $wallet_owned = Tbl_wallet::where([['slot_id', '=', $customer->slot_id], ['currency_id', '=', $buying_currency->currency_id]])->first();
            $from = Request::user()->type;
            if ($gc_owned->wallet_amount >= $gc_payment && $wallet_owned->wallet_amount >= $wallet_payment) {
                //check discounts
                foreach ($order as $key => $value) {
                    $item[$key] = Tbl_item::where('item_id', $value->item_id)->first();
                    $item['discount'][$key] = Cashier::get_customer_discount($customer->slot_id, $value->item_id);
                    $item['discount'][$key]['original_price'] = $item[$key]['item_price'];
                    if ($item['discount'][$key]['percentage'] == 0) {
                        $discount = 'none';
                        if ($gc_payment > 0) {
                            $item_price = $item[$key]['item_gc_price'] * $value->quantity;
                            if ($item_price == 0) {
                                $return["status"] = "error";
                                $return["status_code"] = 420;
                                $return["status_message"] = "You cannot sell an Item through GC that has no GC Price set.";
                            }
                        } else {
                            $item_price = $item[$key]['item_price'] * $value->quantity;
                        }
                    } else {
                        $discount_to_deduct = $item[$key]['item_price'] * ($item['discount'][$key]['percentage'] / 100);
                        $item_price = ($item[$key]['item_price'] - $discount_to_deduct) * $value->quantity;
                        if ($gc_payment > 0) {
                            //no discount
                            $item_price = $item[$key]['item_gc_price'] * $value->quantity;
                            if ($item_price == 0) {
                                $return["status"] = "error";
                                $return["status_code"] = 420;
                                $return["status_message"] = "You cannot sell an Item through GC that has no GC Price set.";
                            }
                        } else {
                            $item_price = ($item[$key]['item_price'] - $discount_to_deduct) * $value->quantity;
                        }

                    }
                    $payable = $payable + $item_price;
                    if ($from == "stockist") {
                        $check_buyer = Tbl_slot::where('slot_id', $customer->slot_id)->first()->slot_owner;
                        $buyer_user_info = Users::where('id', $check_buyer)->first();
                        if ($buyer_user_info->type == 'stockist') {
                            //no discount
                            $item_price = $item[$key]['item_gc_price'] * $value->quantity;
                            if ($item_price == 0) {
                                $return["status"] = "error";
                                $return["status_code"] = 420;
                                $return["status_message"] = "You cannot sell an Item through GC that has no GC Price set.";
                            }
                        } else {
                            $item_price = ($item[$key]['item_price'] - $discount_to_deduct) * $value->quantity;
                        }

                    }
                }

                $combined_payment = $wallet_payment + $gc_payment + $cheque_payment + $cash_payment;

                $manager_discount_amount = 0;
                if ($manager_discount > 0) {
                    $manager_discount_amount = $payable * ($manager_discount / 100);
                }

                $vat_amount = 0;
                if ($vat == 1) {
                    $vat_amount = ($payable - $manager_discount_amount) * 0.12;
                }
                $payable = $vat_amount + $payable - $manager_discount_amount;
                $payment_change = ($combined_payment - $payable);
                if ($payment_change >= 0) {

                    if ($wallet_payment > 0) {
                        Log::insert_wallet($customer->slot_id, ($wallet_payment * -1), "cashier", $buying_currency->currency_id);

                        //---------------------------------Cashier bonus Enable------------------------------------------
                        $total_wallet_payment = $wallet_payment - $payment_change;
                        $check_cashier_bonus = Tbl_cashier_bonus_settings::first() ? Tbl_cashier_bonus_settings::first()->cashier_bonus_enable : 0;
                        if ($check_cashier_bonus != 0) {
                            $cashier_bonus = Tbl_cashier_bonus::where("archive", 0)->orderBy("cashier_bonus_buy_amount", "Desc")->get();
                            if (count($cashier_bonus) != 0) {
                                foreach ($cashier_bonus as $key => $bonus) {
                                    if ($total_wallet_payment >= $bonus["cashier_bonus_buy_amount"]) {
                                        Log::insert_wallet($customer->slot_id, $bonus["cashier_bonus_given_amount"], "cashier", $gc_currency_id->currency_id);
                                        break;
                                    }
                                }
                            }
                        }
                        //--------------------------------------------------------------------------------------
                    }
                    if ($gc_payment > 0) {
                        Log::insert_wallet($customer->slot_id, ($payable * -1), "cashier", $gc_currency_id->currency_id);
                    }
                    if ($cash_payment > 0) {

                        //---------------------------------Cashier bonus Enable------------------------------------------
                        $total_cash_payment = $cash_payment - $payment_change;
                        $check_cashier_bonus = Tbl_cashier_bonus_settings::first() ? Tbl_cashier_bonus_settings::first()->cashier_bonus_enable : 0;
                        if ($check_cashier_bonus != 0) {
                            $cashier_bonus = Tbl_cashier_bonus::where("archive", 0)->orderBy("cashier_bonus_buy_amount", "Desc")->get();
                            if (count($cashier_bonus) != 0) {
                                foreach ($cashier_bonus as $key => $bonus) {
                                    if ($total_cash_payment >= $bonus["cashier_bonus_buy_amount"]) {
                                        Log::insert_wallet($customer->slot_id, $bonus["cashier_bonus_given_amount"], "cashier", $gc_currency_id->currency_id);
                                        break;
                                    }
                                }
                            }
                        }
                        //--------------------------------------------------------------------------------------

                    }

                    $ordered_item = json_encode($order_item);
                    $vat = $vat;
                    $buyer_slot_id = $customer->slot_id;
                    $cashier_user_id = Request::user()->id;

                    $return = Cashier::create_order($ordered_item, $vat, $buyer_slot_id, $cashier_user_id, $from, 'none', $picked_up, $payment_change, $manager_discount, $remarks, null, $payment_method->cashier_payment_method_id, $combined_payment,0,0,0,0,0,$grand_total,null,null,null);
                } else {
                    $return["status"] = "error";
                    $return["status_code"] = 400;
                    $return["status_message"] = "Insufficient Payment.";
                }
            } else {
                $return["status"] = "error";
                $return["status_code"] = 400;
                $return["status_message"] = "Insufficient Wallet/GC!";
            }
        } else {
            $return["status"] = "error";
            $return["status_code"] = 400;
            $return["status_message"] = "Please add payment!";
        }

        return $return;
    }
    public static function recount_inventory()
    {
        $check_user = Users::where('id', Request::user()->id)->first();
        if ($check_user->type == "cashier") {
            $cashier = Tbl_cashier::where('cashier_user_id', $check_user->id)->first();
            $inventory = Tbl_inventory::where('inventory_branch_id', $cashier->cashier_branch_id)->get();
        } else {
            $stockist = DB::table('tbl_stockist')->where('stockist_user_id', $check_user->id)->first();
            $inventory = Tbl_inventory::where('inventory_branch_id', $stockist->stockist_branch_id)->get();
        }
        foreach ($inventory as $key => $value) {
            $available_count = Tbl_codes::where('code_inventory_id', $value->inventory_id)
                ->where('code_sold', 0)
                ->where('code_used', 0)
                ->where('archived', 0)
                ->whereNull('kit_requirement')
                ->count();
            $sold_count = Tbl_codes::where('code_inventory_id', $value->inventory_id)->where('code_sold', 1)->count();
            $total = Tbl_codes::where('code_inventory_id', $value->inventory_id)->count();
            $update['inventory_quantity'] = $available_count;
            $update['inventory_sold'] = $sold_count;
            $update['inventory_total'] = $total;

            Tbl_inventory::where('inventory_id', $value->inventory_id)->update($update);
        }

        $data = 1;
        return $data;
    }

    public static function rate_item($item_rate, $item_id, $user_id, $item_review, $order_number)
    {
        // dd(12342134);
        $query = Tbl_item_rating::where('item_id', $item_id)->where("item_rate_order_number", $order_number)->where('user_id', $user_id)->first();
        $data['item_rate'] = $item_rate;
        $data['item_id'] = $item_id;
        $data['user_id'] = $user_id;
        $data['item_review'] = $item_review;
        $data['item_rate_order_number'] = $order_number;
        $data['item_rate_created'] = Carbon::now();
        if ($item_review != "") {
            $data['item_is_disabled'] = 1;
        }
        if ($query) {
            Tbl_item_rating::where('item_id', $item_id)->where("item_rate_order_number", $order_number)->where('user_id', $user_id)->update($data);
        } else {
            Tbl_item_rating::insert($data);
        }

        return Self::get_ratings($item_id, $user_id, $order_number);
    }

    public static function get_ratings($item_id = null, $user_id = null, $order_number = null)
    {
        if ($item_id != null && $user_id == null) {
            $array = Tbl_item_rating::where('item_id', $item_id);

            $data['rating_sum'] = $array->count() == 0 ? 0 : $array->sum('item_rate');
            $data['rating_list'] = $array->count() == 0 ? 0 : $array->MemberRatings()->get();
            $data['rating_count'] = $array->count() == 0 ? 0 : $array->count();
            $data['rating_average'] = $array->count() == 0 ? 0 : round($data['rating_sum'] / $data['rating_count']);

            $query = $data;
        } else {
            $query = Tbl_item_rating::where('item_id', $item_id)->where("item_rate_order_number", $order_number)->where('user_id', $user_id)->first();

            if (!$query) {
                $data['item_rate'] = 0;
                $data['item_id'] = $item_id;
                $data['user_id'] = $user_id;
                $data['item_review'] = "";
                $data['item_rate_order_number'] = null;
                $data['item_is_disabled'] = 0;
                $query = $data;
            }

        }

        return $query;
    }
    public static function get_currency()
    {
        return Tbl_currency::where('archive', 0)->get();
    }

    public static function check_rel_item_kit($item_id, $branch_id)
    {
        $max_code = 1000;
        $rel_item_kit = Rel_item_kit::where('rel_item_kit.item_id', $item_id)->join('tbl_item', 'tbl_item.item_id', '=', 'rel_item_kit.item_inclusive_id')->get();

        if (count($rel_item_kit) > 0) {

            foreach ($rel_item_kit as $key => $rel_kit) {
                $rel_item_kit[$key]->inventory_quantity = Tbl_inventory::where('inventory_item_id', $rel_kit->item_inclusive_id)->where('inventory_branch_id', $branch_id)->value('inventory_quantity');
                $rel_item_kit[$key]->maximum_codes = $rel_item_kit[$key]->inventory_quantity / $rel_kit->item_qty;
                if ($rel_item_kit[$key]->maximum_codes < $max_code) {
                    $max_code = $rel_item_kit[$key]->maximum_codes;
                }
            }
        }
        $data['max_code'] = $max_code;
        $data['rel_item_kit'] = $rel_item_kit;
        return $data;
    }
    public static function load_island_group()
    {
        $response = Tbl_island_group::get();

        return $response;
    }
    public static function load_shipping_fee()
    {
        $ninja = Tbl_ninja_van::leftjoin('tbl_island_group', 'tbl_island_group.id', 'island_group_id')->select('island_group', 'item_fee as ninja_item_fee', 'qty_fee as ninja_qty_fee')->get();
        $lalamove = Tbl_lalamove::leftjoin('tbl_island_group', 'tbl_island_group.id', 'island_group_id')->select('island_group', 'item_fee as lalamove_item_fee', 'qty_fee as lalamove_qty_fee')->get();

        foreach ($ninja as $key => $value) {

            $ninja[$key]['lalamove_item_fee'] = Tbl_lalamove::where('id', '')->select('item_fee')->first();
            $ninja[$key]['lalamove_qty_fee'] = Tbl_lalamove::where('id', '')->select('qty_fee')->first();
        }
        // dd(array($ninja));
        $response = array($ninja) + array($lalamove);
        // dd($response);
        return $ninja;
    }
    public static function get_category_list()
    {
        $response = Tbl_product_category::where('archive', 0)->get();
        return $response;
    }
    public static function get_subcategory_list($category_id = null)
    {
        $response = Tbl_product_subcategory::where('category_id', $category_id)->where('archive', 0)->get();

        return $response;
    }
    public static function highest_membership_list()
    {
        $return['highest_membership'] = Tbl_membership::where('archive', 0)->orderBy('hierarchy', 'DESC')->pluck('membership_id')->first();

        if ($return['highest_membership']) {
            $return['second_highest_membership'] = Tbl_membership::where('archive', 0)->where('membership_id', '!=', $return['highest_membership'])->orderBy('hierarchy', 'DESC')->pluck('membership_id')->first();
        } else {
            $return['second_highest_membership'] = Tbl_membership::where('archive', 0)->orderBy('hierarchy', 'DESC')->pluck('membership_id')->first();
        }

        return $return;
    }
    public static function load_team_sales_bonus_level($item_id)
    {
        $data["settings"] = [];
        $get = Tbl_team_sales_bonus_level::where('item_id', $item_id)->get();
        $membership = Tbl_membership::where("archive", 0)->get();

        $data["settings"]["team_sales_bonus_settings"] = [];
        $data["settings"]["membership_level"] = [];

        foreach ($membership as $memb) {
            $data["settings"]["membership_level"][$memb->membership_id] = array_fill(0, $memb->team_sales_bonus_level, "");
        }

        foreach ($membership as $memb) {
            foreach ($membership as $memb2) {
                for ($level = 1; $level <= $memb->team_sales_bonus_level; $level++) {
                    $earnings = Tbl_team_sales_bonus_level::where('item_id', $item_id)->where("membership_id", $memb->membership_id)->where("membership_entry_id", $memb2->membership_id)->where("membership_level", $level)->first();
                    $earnings = $earnings ? $earnings->team_sales_bonus : 0;

                    $data["settings"]["team_sales_bonus_settings"][$memb->membership_id][$memb2->membership_id][$level] = $earnings;
                }
            }
        }
        return $data;
    }
}
