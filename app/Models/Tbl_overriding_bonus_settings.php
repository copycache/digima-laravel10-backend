<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Tbl_overriding_bonus_settings extends Model
{
    use HasFactory;

	protected $table = 'tbl_overriding_bonus_settings';

	protected $primaryKey = "id";

	protected $guarded = [];
    public $timestamps = false;
}
