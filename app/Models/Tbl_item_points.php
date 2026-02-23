<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Tbl_item_points extends Model
{
    use HasFactory;

	protected $guarded = [];

	protected $table = 'tbl_item_points';
	protected $primaryKey = "item_points_id";
    public $timestamps = false;
}
