<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Tbl_lockdown_logs extends Model
{
    use HasFactory;

	protected $guarded = [];

	protected $table = 'tbl_lockdown_logs';
	protected $primaryKey = "lock_down_id";
    public $timestamps = false;
}
