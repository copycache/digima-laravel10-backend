<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Tbl_binary_projected_income_log extends Model
{
    use HasFactory;

	protected $table = 'tbl_binary_projected_income_log';
	protected $primaryKey = "log_id";
    public $timestamps = false;

	protected $guarded = [];

}
