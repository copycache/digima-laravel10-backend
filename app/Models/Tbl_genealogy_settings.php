<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Tbl_genealogy_settings extends Model
{
    use HasFactory;

    protected $table = 'tbl_genealogy_settings';
	protected $primaryKey = "genealogy_settings_id";
    public $timestamps = false;
}
