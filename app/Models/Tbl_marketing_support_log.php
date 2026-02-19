<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Tbl_marketing_support_log extends Model
{
    use HasFactory;

	protected $table = 'tbl_marketing_support_log';
	protected $primaryKey = "log_id";
    public $timestamps = false;
}
