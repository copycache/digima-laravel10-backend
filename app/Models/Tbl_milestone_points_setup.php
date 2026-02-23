<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Tbl_milestone_points_setup extends Model
{
    use HasFactory;

	protected $primaryKey = "id";

	protected $guarded = [];

	protected $table = 'tbl_milestone_points_setup';
    public $timestamps = false;
}
