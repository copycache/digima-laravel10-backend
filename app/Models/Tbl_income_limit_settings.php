<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Tbl_income_limit_settings extends Model
{
    use HasFactory;

    protected $table = 'tbl_income_limit_settings';
	protected $primaryKey = "income_limit_id";
    public $timestamps = false;

    protected $guarded = [];

}
