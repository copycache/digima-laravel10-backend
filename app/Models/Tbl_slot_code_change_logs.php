<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Tbl_slot_code_change_logs extends Model
{
    use HasFactory;

	protected $table = 'tbl_slot_code_change_logs';
	protected $primaryKey = "slot_code_changes_log_id";
    public $timestamps = false;

    public function scopeOwner($query)
    {
    	 return $query->join('users', 'users.id', '=', 'tbl_slot_code_change_logs.user_id');
    }
}
