<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Tbl_sponsor_matching extends Model
{
    use HasFactory;

	protected $guarded = [];

	protected $table = 'tbl_sponsor_matching';
	protected $primaryKey = "tbl_sponsor_matching_id";
    public $timestamps = false;
}
