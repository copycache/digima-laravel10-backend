<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Tbl_vortex_slot extends Model
{
    use HasFactory;

	protected $table = 'tbl_vortex_slot';
	protected $primaryKey = "vortex_slot_id";
    public $timestamps = false;
}
