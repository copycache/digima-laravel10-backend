<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Tbl_membership_unilevel_or_level extends Model
{
    use HasFactory;

	protected $primaryKey = "id";

	protected $guarded = [];

	protected $table = 'tbl_membership_unilevel_or_level';
    public $timestamps = false;
}
