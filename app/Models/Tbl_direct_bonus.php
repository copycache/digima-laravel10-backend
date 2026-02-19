<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Tbl_direct_bonus extends Model
{
    use HasFactory;

	protected $table = 'tbl_direct_bonus';
	protected $primaryKey = "direct_bonus_id";
    public $timestamps = false;
}
