<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Tbl_binary_points_settings extends Model
{
    use HasFactory;

	protected $table = 'tbl_binary_points_settings';
    public $timestamps = false;

	protected $primaryKey = "id";

	protected $guarded = [];


}
