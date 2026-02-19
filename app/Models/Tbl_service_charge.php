<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Tbl_service_charge extends Model
{
    use HasFactory;

	protected $table = 'tbl_service_charge';
	protected $primaryKey = "service_id";
    public $timestamps = false;
}
