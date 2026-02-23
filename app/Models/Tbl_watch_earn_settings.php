<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Tbl_watch_earn_settings extends Model
{
    use HasFactory;

	protected $guarded = [];

	protected $table = 'tbl_watch_earn_settings';
	protected $primaryKey = "watch_earn_settings_id";
    public $timestamps = false;
}
