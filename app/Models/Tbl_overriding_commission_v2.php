<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Tbl_overriding_commission_v2 extends Model
{
    use HasFactory;

	protected $table = 'tbl_overriding_commission_v2';
    public $timestamps = false;
}
