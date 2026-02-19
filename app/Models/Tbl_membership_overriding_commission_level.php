<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Tbl_membership_overriding_commission_level extends Model
{
    use HasFactory;

	protected $table = 'tbl_membership_overriding_commission_level';
    public $timestamps = false;
}
