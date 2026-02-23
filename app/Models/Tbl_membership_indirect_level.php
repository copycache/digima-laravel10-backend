<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Tbl_membership_indirect_level extends Model
{
    use HasFactory;

	protected $primaryKey = "id";

	protected $guarded = [];

	protected $table = 'tbl_membership_indirect_level';
    public $timestamps = false;
}
