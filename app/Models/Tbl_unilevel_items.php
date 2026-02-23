<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Tbl_unilevel_items extends Model
{
    use HasFactory;

	protected $guarded = [];

	protected $table = 'tbl_unilevel_items';
	protected $primaryKey = "tbl_unilevel_items_id";
    public $timestamps = false;
}
