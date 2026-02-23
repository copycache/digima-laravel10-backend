<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Tbl_ninja_van extends Model
{
    use HasFactory;

	protected $table = 'tbl_ninja_van';

	protected $primaryKey = "id";

	protected $guarded = [];
    public $timestamps = false;
}
