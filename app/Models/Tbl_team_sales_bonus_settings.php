<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Tbl_team_sales_bonus_settings extends Model
{
    use HasFactory;

	protected $table = 'tbl_team_sales_bonus_settings';
	protected $primaryKey = "id";
    public $timestamps = false;
}
