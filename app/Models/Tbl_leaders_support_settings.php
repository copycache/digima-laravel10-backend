<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Tbl_leaders_support_settings extends Model
{
    use HasFactory;

	protected $guarded = [];

	protected $table = 'tbl_leaders_support_settings';
	protected $primaryKey = "settings_id";
    public $timestamps = true;
}
