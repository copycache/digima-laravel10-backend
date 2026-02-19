<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Tbl_mlm_board_slot extends Model
{
    use HasFactory;

    protected $table = 'tbl_mlm_board_slot';
    protected $primaryKey = "board_slot_id";
    public $timestamps = false;

    public function scopeOwner($query)
    {   

       $query = $query->join('tbl_slot', 'tbl_slot.slot_id', '=', 'tbl_mlm_board_slot.slot_id')->join('users', 'users.id', '=', 'tbl_slot.slot_owner');
       return $query;
    }
    public function scopeJoinMembership($query)
    {
        return $query->join('tbl_membership', 'tbl_membership.membership_id', '=', 'tbl_slot.slot_membership');
    }
}
