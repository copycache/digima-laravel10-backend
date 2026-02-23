<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Tbl_stairstep_settings extends Model
{
    use HasFactory;

	protected $guarded = [];

	protected $table = 'tbl_stairstep_settings';
	protected $primaryKey = "stairstep_settings_id";
    public $timestamps = false;
}
