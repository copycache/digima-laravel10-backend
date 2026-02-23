<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Tbl_tree_sponsor extends Model
{
    use HasFactory;

	protected $guarded = [];

	protected $table = 'tbl_tree_sponsor';
	protected $primaryKey = "tree_sponsor_id";
    public $timestamps = false;

    public function scopeChild($query)
    {
    	return $query->leftJoin("tbl_slot","tbl_slot.slot_id","=","tbl_tree_sponsor.sponsor_child_id");
    }

    public function scopeParent($query)
    {
    	return $query->leftJoin("tbl_slot","tbl_slot.slot_id","=","tbl_tree_sponsor.sponsor_parent_id");
    }
     public function scopeMembership($query)
    {
    	return $query->leftJoin("tbl_membership","tbl_membership.membership_id","=","tbl_slot.slot_membership");
    }
     public function scopeRank($query)
    {
    	return $query->leftJoin("tbl_stairstep_rank","tbl_stairstep_rank.stairstep_rank_id","=","tbl_slot.slot_stairstep_rank");
    }
    public function scopeOwner($query)
    {
    	 return $query->join('users', 'users.id', '=', 'tbl_slot.slot_owner');
    }
}
