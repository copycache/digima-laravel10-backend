<?php
namespace App\Globals;
use App\Models\Tbl_tree_sponsor;
use App\Models\Tbl_tree_placement;
use App\Models\Tbl_slot;
use App\Models\Tbl_mlm_board_placement;
use App\Models\Tbl_mlm_board_slot;
use App\Models\Tbl_matrix_placement;


use DB;
class Tree
{	
    public static function insert_tree_placement($slot_info, $new_slot, $level,$placement=null)
    { 
        if($slot_info != null)
        {
            $old_level   = $level;
            $upline_info = Tbl_slot::where("slot_id",$slot_info->slot_placement)->first();
            
            /*CHECK IF TREE IS ALREADY EXIST*/
            if($upline_info)
            {
                $check_if_exist = Tbl_tree_placement::where("placement_child_id",$new_slot->slot_id)
                ->where('placement_level', '=', $level)
                ->where('placement_parent_id', '=', $upline_info->slot_id)
                ->first();
            }
            else
            {
                $check_if_exist = Tbl_tree_placement::where("placement_child_id",$new_slot->slot_id)
                ->where('placement_level', '=', $level)
                ->first();
            }

            if($upline_info)
            {
                if($upline_info->slot_id != $new_slot->slot_id)
                {
                    if(!$check_if_exist)
                    {   
                        $insert["placement_parent_id"] = $upline_info->slot_id;
                        $insert["placement_child_id"] = $new_slot->slot_id;
                        $insert["placement_position"] = $slot_info->slot_position;
                        $insert["placement_level"] = $level;

                        if($level==1)
                        {
                            $insert["position_type"] = "OUTER";
                        } else {
                            $d = Tbl_tree_placement::where('placement_parent_id', $upline_info->slot_id)->where('placement_child_id', $new_slot->slot_placement)->first();
                            if($d)
                            {
                                if($d->position_type == "OUTER")
                                {
                                    if($d->placement_position == $new_slot->slot_position)
                                    {
                                        $insert["position_type"] = "OUTER";
                                    } else {
                                        $insert["position_type"] = "INNER";
                                    }
                                } else {
                                    $insert["position_type"] = "INNER";
                                }
                            }
                        }
                        Tbl_tree_placement::insert($insert);
                    }

                    $level++;
                    Tree::insert_tree_placement($upline_info, $new_slot, $level);
                } 
            }
        }
    }

    public static function insert_board_placement($placement, $slot_id, $placement_level, $board_level = 1)
    { 
        $old_level   = $placement_level;
        $upline_info = Tbl_mlm_board_slot::where('slot_id', $placement)->where('board_level', $board_level)->first();
        $child       = Tbl_mlm_board_slot::where('slot_id', $slot_id)->where('board_level', $board_level)->first();
        /*CHECK IF TREE IS ALREADY EXIST*/
        if($child)
        {
            if($upline_info)
            {
                $check_if_exist = Tbl_mlm_board_placement::where("placement_child_id",$child->slot_id)
                ->where('placement_level', '=', $placement_level)
                ->where('placement_parent_id', '=', $upline_info->placement_child_id)
                ->first();
            }
            else
            {
                $check_if_exist = Tbl_mlm_board_placement::where("placement_child_id",$child->slot_id)
                ->where('placement_level', '=', $placement_level)
                ->first();
            }

        
            if($upline_info)
            {
                if($upline_info->slot_id != $child->slot_id)
                {
                    if(!$check_if_exist)
                    {   
                        $insert["placement_parent_id"] = $upline_info->slot_id;
                        $insert["placement_child_id"] = $child->slot_id;
                        $insert["placement_position"] = $child->placement_position;
                        $insert["placement_level"] = $placement_level;
                        $insert["board_level"] = $board_level;
                        Tbl_mlm_board_placement::insert($insert);       
                    }

                    $placement_level++;
                    $parent_upline = Tbl_mlm_board_slot::where('slot_id', $upline_info->placement)->where('board_level', $board_level)->first();
                    if($parent_upline)
                    {
                        Tree::insert_board_placement($parent_upline->slot_id, $slot_id, $placement_level, $board_level);        
                    }
                } 
            }
        }
        
        
        // if($slot_info != null)
        // {
        //     if($board_level == 1)
        //     {
        //         $slot_info   = Tbl_slot::where("slot_id",$slot_info->slot_id)->first();
        //         $old_level   = $level;
        //         $upline_info = Tbl_slot::where("slot_id",$slot_info->slot_placement)->first();
                
        //         /*CHECK IF TREE IS ALREADY EXIST*/
        //         if($upline_info)
        //         {
        //             $check_if_exist = DB::table('tbl_mlm_board_placement')->where("placement_child_id",$new_slot->slot_id)
        //             ->where('placement_level', '=', $level)
        //             ->where('placement_parent_id', '=', $upline_info->slot_id)
        //             ->where('board_level', $board_level)
        //             ->first();
        //         }
        //         else
        //         {
        //             $check_if_exist = DB::table('tbl_mlm_board_placement')->where("placement_child_id",$new_slot->slot_id)
        //             ->where('placement_level', '=', $level)
        //             ->where('board_level', $board_level)
        //             ->first();
        //         }

        //         if($upline_info)
        //         {
        //             if($upline_info->placement_parent_id != $new_slot->slot_id)
        //             {
        //                 if(!$check_if_exist)
        //                 {   
        //                     $insert["placement_parent_id"] = $upline_info->slot_id;
        //                     $insert["placement_child_id"] = $new_slot->slot_id;
        //                     $insert["placement_position"] = $slot_info->slot_position;
        //                     $insert["placement_level"] = $level;
        //                     $insert["board_level"]     = $board_level;
        //                     DB::table('tbl_mlm_board_placement')->insert($insert);       
        //                 }

        //                 $level++;
        //                 Tree::insert_board_placement($upline_info, $new_slot, $level);        
        //             } 
        //         }
        //     }
        //     else
        //     {
        //         if($level == 1)
        //         {
        //             Tbl_mlm_board_placement::where('placement_parent_id', $slot_info->slot_id)->update(['graduated' => 1]);

        //             $slot_info = Slot::get_board_auto_position($board_level);
        //             $insert["placement_parent_id"] = $slot_info->slot_id;
        //             $insert["placement_child_id"] = $new_slot->placement_child_id;
        //             $insert["placement_position"] = $slot_info->position;
        //             $insert["placement_level"] = $level;
        //             $insert["board_level"]     = $board_level;
        //             DB::table('tbl_mlm_board_placement')->insert($insert);
        //             $level++;
        //             Tree::insert_board_placement($slot_info, $new_slot, $level);     
        //         }
        //         else
        //         {
        //             $old_level   = $level;
        //             $upline_info = Tbl_mlm_board_placement::where('placement_child_id', $slot_info->slot_id)->where('placement_level', 1)->first();
            
        //             /*CHECK IF TREE IS ALREADY EXIST*/
        //             if($upline_info)
        //             {
        //                 $check_if_exist = DB::table('tbl_mlm_board_placement')->where("placement_child_id",$new_slot->slot_id)
        //                 ->where('placement_level', '=', $level)
        //                 ->where('placement_parent_id', '=', $upline_info->slot_id)
        //                 ->where('board_level', $board_level)
        //                 ->first();
        //             }
        //             else
        //             {
        //                 $check_if_exist = DB::table('tbl_mlm_board_placement')->where("placement_child_id",$new_slot->slot_id)
        //                 ->where('placement_level', '=', $level)
        //                 ->where('board_level', $board_level)
        //                 ->first();
        //             }

        //             if($upline_info)
        //             {
        //                 if($upline_info->placement_parent_id != $new_slot->slot_id)
        //                 {
        //                     if(!$check_if_exist)
        //                     {   
        //                         Tbl_mlm_board_placement::where('placement_parent_id', $upline_info->placement_parent_id)->update(['graduated' => 1]);
        //                         $insert["placement_parent_id"] = $upline_info->placement_parent_id;
        //                         $insert["placement_child_id"] = $new_slot->slot_id;
        //                         $insert["placement_position"] = $slot_info->slot_position;
        //                         $insert["placement_level"] = $level;
        //                         $insert["board_level"]     = $board_level;
        //                         DB::table('tbl_mlm_board_placement')->insert($insert);       
        //                     }

        //                     $level++;
        //                     Tree::insert_board_placement($upline_info, $new_slot, $level);        
        //                 } 
        //             }
        //         }
        //     }
        // }
    }

    public static function insert_tree_sponsor($slot_info, $new_slot, $level)
    {

        if($slot_info != null)
        {
            $upline_info = Tbl_slot::where("slot_id",$slot_info->slot_sponsor)->first();
            /*CHECK IF TREE IS ALREADY EXIST*/
            $check_if_exist = null;
            if($upline_info)
            {
                $check_if_exist = Tbl_tree_sponsor::where("sponsor_child_id",$new_slot->slot_id)
                ->where('sponsor_parent_id', '=', $upline_info->slot_id )
                ->first();
            }
            else
            {
                $check_if_exist = Tbl_tree_sponsor::where("sponsor_child_id",$new_slot->slot_id)
                ->first();
            }

            if($upline_info)
            {
                    if($upline_info)
                    {
                        if($upline_info->slot_id != $new_slot->slot_id)
                        {
                            if(!$check_if_exist)
                            {                            
                            	$insert["sponsor_parent_id"] = $upline_info->slot_id;
                                $insert["sponsor_child_id"] = $new_slot->slot_id;
                                $insert["sponsor_level"] = $level;
                                Tbl_tree_sponsor::insert($insert);
                            }
                            $level++;
                            Tree::insert_tree_sponsor($upline_info, $new_slot, $level);  
                        }
                    }
            }
        }
    }

    public static function insert_matrix_placement($slot_info, $new_slot, $level,$placement=null)
    {
        if($slot_info != null)
        {   
            $upline_info = Tbl_slot::where("slot_id",$slot_info->matrix_sponsor)->first();
            /*CHECK IF TREE IS ALREADY EXIST*/
            if($upline_info)
            {
                $check_if_exist = Tbl_matrix_placement::where("child_id",$new_slot->slot_id)
                ->where('level', '=', $level)
                ->where('parent_id', '=', $upline_info->slot_id)
                ->first();
            }
            else
            {
                $check_if_exist = Tbl_matrix_placement::where("child_id",$new_slot->slot_id)
                ->where('level', '=', $level)
                ->first();
            }

            if($upline_info)
            {
                if($upline_info->slot_id != $new_slot->slot_id)
                {
                    if(!$check_if_exist)
                    {   
                        $insert["parent_id"] = $upline_info->slot_id;
                        $insert["child_id"] = $new_slot->slot_id;
                        $insert["position"] = $slot_info->matrix_position;
                        $insert["level"] = $level;
                        Tbl_matrix_placement::insert($insert);
                    }
                    $level++;
                    Tree::insert_matrix_placement($upline_info, $new_slot, $level);
                }
            }
        }
    }
}
