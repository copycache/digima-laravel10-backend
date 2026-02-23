<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Tbl_incentive_setup extends Model
{
    use HasFactory;

	protected $table = 'tbl_incentive_setup';
	protected $primaryKey = "setup_id";
    public $timestamps = false;

	protected $guarded = [];

}
