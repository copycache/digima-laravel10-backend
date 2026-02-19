<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Tbl_mlm_unilevel_settings extends Model
{
    use HasFactory;

	protected $table = 'tbl_mlm_unilevel_settings';
	protected $primaryKey = "mlm_unilevel_settings_id";
    public $timestamps = false;
}
