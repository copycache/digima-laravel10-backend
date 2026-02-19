<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Tbl_matrix_placement extends Model
{
    use HasFactory;

	protected $table = 'tbl_matrix_placement';
	protected $primaryKey = "matrix_id";
    public $timestamps = false;

	public function scopeChild($query)
    {
    	return $query->leftJoin("tbl_slot","tbl_slot.slot_id","=","tbl_matrix_placement.child_id");
    }

	public function scopeOwner($query)
    {
    	 return $query->join('users', 'users.id', '=', 'tbl_slot.slot_owner');
    }
	public function scopeMembership($query)
	{
		return $query->join("tbl_membership","tbl_membership.membership_id","=","tbl_slot.slot_membership");
	}
}
