<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Tbl_flushout_log extends Model
{
    use HasFactory;

	protected $table = 'tbl_flushout_log';
	protected $primaryKey = "flushout_log_id";
    public $timestamps = false;

	protected $guarded = [];

}
