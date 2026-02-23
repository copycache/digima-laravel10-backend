<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Tbl_membership_upgrade_settings extends Model
{
    use HasFactory;
	protected $guarded = [];

	protected $primaryKey = 'membership_upgrade_settings_id';
	protected $table = 'tbl_membership_upgrade_settings';
    public $timestamps = false;
}
