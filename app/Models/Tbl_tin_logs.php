<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Tbl_tin_logs extends Model
{
    use HasFactory;

	protected $table = 'tbl_tin_logs';
	protected $primaryKey = "tin_logs_id";
    public $timestamps = false;
}
