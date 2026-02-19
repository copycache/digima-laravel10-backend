<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Tbl_bank extends Model
{
    use HasFactory;

	protected $table = 'tbl_bank';
	protected $primaryKey = "bank_id";
    public $timestamps = false;
}
