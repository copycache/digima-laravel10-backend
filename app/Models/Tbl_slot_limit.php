<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Tbl_slot_limit extends Model
{
    use HasFactory;

	protected $guarded = [];

	protected $table = 'tbl_slot_limit';
	protected $primaryKey = "user_id";
    public $timestamps = false;
}
