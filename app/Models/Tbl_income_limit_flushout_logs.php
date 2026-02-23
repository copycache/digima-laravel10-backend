<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Tbl_income_limit_flushout_logs extends Model
{
    use HasFactory;

    protected $table = 'tbl_income_limit_flushout_logs';
	protected $primaryKey = "income_limit_flushout_logs_id";
    public $timestamps = false;

    protected $guarded = [];

}
