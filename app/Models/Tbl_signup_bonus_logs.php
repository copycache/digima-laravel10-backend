<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Tbl_signup_bonus_logs extends Model
{
    use HasFactory;

	protected $table        = 'tbl_signup_bonus_logs';
	protected $primaryKey   = "id";
    public $timestamps      = false;
}
