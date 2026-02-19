<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Tbl_vortex_token_log extends Model
{
    use HasFactory;

	protected $table = 'tbl_vortex_token_log';
	protected $primaryKey = "vortex_token_log_id";
    public $timestamps = false;
}
