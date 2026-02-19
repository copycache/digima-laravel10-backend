<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Tbl_watched_videos extends Model
{
    use HasFactory;

	protected $table = 'tbl_watched_videos';
	protected $primaryKey = "watched_id";
    public $timestamps = false;
}
