<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Tbl_mlm_pass_up_settings extends Model
{
    use HasFactory;

	protected $guarded = [];

	protected $table = 'tbl_mlm_pass_up_settings';
	protected $primaryKey = "pass_up_settings_id";
    public $timestamps = false;
}
