<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Tbl_unilevel_distribute extends Model
{
    use HasFactory;

	protected $guarded = [];

	protected $table = 'tbl_unilevel_distribute';
	protected $primaryKey = "unilevel_distribute_id";
    public $timestamps = false;
}
