<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Tbl_admin extends Model
{
    use HasFactory;

	protected $table = 'tbl_admin';
	protected $primaryKey = "admin_id";
    public $timestamps = false;

	protected $guarded = [];
}
