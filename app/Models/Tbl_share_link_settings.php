<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Tbl_share_link_settings extends Model
{
    use HasFactory;

	protected $table = 'tbl_share_link_settings';
	protected $primaryKey = "share_link_settings_id";
    public $timestamps = false;
}
