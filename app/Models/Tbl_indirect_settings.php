<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Tbl_indirect_settings extends Model
{
    use HasFactory;

    protected $table = 'tbl_indirect_settings';
	protected $primaryKey = "indirect_settings_id";
    public $timestamps = false;

    protected $guarded = [];

}
