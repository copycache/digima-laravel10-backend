<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Tbl_item_rating extends Model
{
    use HasFactory;

	protected $guarded = [];

	protected $table = 'tbl_item_rating';
	protected $primaryKey = "item_rate_id";
    public $timestamps = false;


    public function scopeMemberRatings($query)
    {
    	$query->join('users','users.id','=','tbl_item_rating.user_id');

    	return $query;
    }
}

