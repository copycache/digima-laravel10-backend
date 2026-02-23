<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Tbl_leaders_support_log extends Model
{
    use HasFactory;

	protected $guarded = [];

	protected $table = 'tbl_leaders_support_log';
	protected $primaryKey = "log";
    public $timestamps = false;
}
