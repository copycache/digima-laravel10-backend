<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Tbl_binary_settings extends Model
{
    use HasFactory;

	protected $table = 'tbl_binary_settings';
	protected $primaryKey = "binary_settings_id";
    public $timestamps = false;
}
