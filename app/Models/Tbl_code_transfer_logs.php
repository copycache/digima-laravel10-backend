<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Tbl_code_transfer_logs extends Model
{
    use HasFactory;

	protected $table = 'tbl_code_transfer_logs';
	protected $primaryKey = "code_transfer_log_id";
    public $timestamps = false;

	protected $guarded = [];

}
