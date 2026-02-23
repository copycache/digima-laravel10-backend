<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Tbl_mlm_universal_pool_bonus_maintain_settings extends Model
{
    use HasFactory;

	protected $guarded = [];

	protected $table = 'tbl_mlm_universal_pool_bonus_maintain_settings';
	protected $primaryKey = "universal_pool_settings_id";
    public $timestamps = false;
}
