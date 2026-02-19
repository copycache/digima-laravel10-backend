<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Tbl_eloading_tab_settings extends Model
{
    use HasFactory;

	protected $table = 'tbl_eloading_tab_settings';
	protected $primaryKey = "eloading_tab_id";
    public $timestamps = false;
}
