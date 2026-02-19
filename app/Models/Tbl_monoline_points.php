<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Tbl_monoline_points extends Model
{
    use HasFactory;

	protected $table = 'tbl_monoline_points';
	protected $primaryKey = "slot_id";
    public $timestamps = false;
}
