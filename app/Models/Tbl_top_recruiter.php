<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;
use DB;
class Tbl_top_recruiter extends Model
{
    use HasFactory;

	protected $table = 'tbl_top_recruiter';

	protected $primaryKey = "id";

	protected $guarded = [];
    public $timestamps = false;

    public function scopeJoinOwner($query)
    {
    	return $query->join('users', 'users.id', '=', 'tbl_slot.slot_owner');
    }

    public function scopeJoinSlot($query)
    {
    	return $query->join('tbl_slot', 'tbl_slot.slot_owner', '=', 'tbl_top_recruiter.slot_id');
    }
}
