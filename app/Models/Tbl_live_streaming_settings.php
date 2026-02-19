<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Tbl_live_streaming_settings extends Model
{
    use HasFactory;

	protected $table = 'tbl_live_streaming_settings';
	protected $primaryKey = "id";
    public $timestamps = false;
}
