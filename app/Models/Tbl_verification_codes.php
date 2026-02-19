<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Tbl_verification_codes extends Model
{
    use HasFactory;

	protected $table = 'tbl_verification_codes';
	protected $primaryKey = "id";
    public $timestamps = false;
}
