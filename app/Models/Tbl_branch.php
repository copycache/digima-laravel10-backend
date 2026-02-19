<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Tbl_branch extends Model
{
    use HasFactory;

	protected $table = 'tbl_branch';
	protected $primaryKey = "branch_id";
    public $timestamps = false;
}
