<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Tbl_other_settings extends Model
{
    use HasFactory;

	protected $table = 'tbl_other_settings';
	protected $primaryKey = "other_settings_id";
    public $timestamps = false;
}
