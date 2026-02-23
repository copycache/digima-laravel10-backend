<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Tbl_binary_points extends Model
{
    use HasFactory;

	protected $table = 'tbl_binary_points';
	protected $primaryKey = "binary_points_id";
    public $timestamps = false;

	protected $guarded = [];

}
