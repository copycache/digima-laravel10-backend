<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Tbl_remittance extends Model
{
    use HasFactory;

	protected $table = 'tbl_remittance';
	protected $primaryKey = "remittance_id";
    public $timestamps = false;
}
