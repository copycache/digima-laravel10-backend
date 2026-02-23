<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Tbl_milestone_bonus_settings extends Model
{
    use HasFactory;

	protected $guarded = [];

	protected $table = 'tbl_milestone_bonus_settings';
	protected $primaryKey = "milestone_settings_id";
    public $timestamps = true;
}
