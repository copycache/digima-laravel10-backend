<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Tbl_user_process extends Model
{
    use HasFactory;

	protected $table = 'tbl_user_process';
	protected $primaryKey = "user_process_id";
    public $timestamps = false;
}
