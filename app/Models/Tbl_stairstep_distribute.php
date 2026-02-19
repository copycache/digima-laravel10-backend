<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Tbl_stairstep_distribute extends Model
{
    use HasFactory;

	protected $table = 'tbl_stairstep_distribute';
	protected $primaryKey = "stairstep_distribute_id";
    public $timestamps = false;
}
