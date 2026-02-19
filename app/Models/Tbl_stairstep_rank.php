<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Tbl_stairstep_rank extends Model
{
    use HasFactory;

	protected $table = 'tbl_stairstep_rank';
	protected $primaryKey = "stairstep_rank_id";
    public $timestamps = false;
}
