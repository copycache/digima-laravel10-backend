<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Tbl_retailer_override_logs extends Model
{
    use HasFactory;

	protected $table = 'tbl_retailer_override_logs';
	protected $primaryKey = "id";
    public $timestamps = false;
}
