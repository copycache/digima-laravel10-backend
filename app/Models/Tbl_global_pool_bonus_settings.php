<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Tbl_global_pool_bonus_settings extends Model
{
    use HasFactory;

    protected $table = 'tbl_global_pool_bonus_settings';
	protected $primaryKey = "global_pool_bonus_id";
    public $timestamps = false;
}
