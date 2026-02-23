<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Tbl_membership_income extends Model
{
    use HasFactory;

	protected $primaryKey = "id";

	protected $guarded = [];

	protected $table = 'tbl_membership_income';
    public $timestamps = false;
}
