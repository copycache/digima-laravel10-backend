<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Tbl_video extends Model
{
    use HasFactory;

	protected $table = 'tbl_video';
	protected $primaryKey = "video_id";
    public $timestamps = false;
}
