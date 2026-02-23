<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Tbl_livewell_rank extends Model
{
    use HasFactory;

	protected $guarded = [];

	protected $table = 'tbl_livewell_rank';
	protected $primaryKey = "livewell_rank_id";
    public $timestamps = false;
}
