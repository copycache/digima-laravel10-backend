<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Tbl_mlm_settings extends Model
{
    use HasFactory;

	protected $guarded = [];

	protected $table = 'tbl_mlm_settings';
	protected $primaryKey = "mlm_settings_id";
    public $timestamps = false;
}
