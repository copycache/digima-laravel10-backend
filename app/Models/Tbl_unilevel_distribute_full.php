<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Tbl_unilevel_distribute_full extends Model
{
    use HasFactory;

	protected $table = 'tbl_unilevel_distribute_full';
	protected $primaryKey = "distribute_full_id";
    public $timestamps = false;
}
