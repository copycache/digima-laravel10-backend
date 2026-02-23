<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Tbl_cash_out_schedule extends Model
{
    use HasFactory;

	protected $table = 'tbl_cash_out_schedule';
	protected $primaryKey = "schedule_id";
    public $timestamps = false;

	protected $guarded = [];

}
