<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;
class Users extends Model
{
    use HasFactory;

protected $table = 'users';
protected $primaryKey = "id";
    public $timestamps = false;

    public function scopeJoinSlot($query)
    {
    return $query->join('tbl_slot', 'tbl_slot.slot_owner', '=', 'users.id');

    return $query;
    }

    public function scopeGetWeekRegistered($query)
    {
    $query ->where('created_at', '>', Carbon::now()->startOfWeek())
    ->where('created_at', '<', Carbon::now()->endOfWeek());

    return $query;
    }

    public function scopeJoinPosition($query)
    {
        $query->join('tbl_position', 'tbl_position.position_id', '=', 'users.position_id');

        return $query;
    }
}
