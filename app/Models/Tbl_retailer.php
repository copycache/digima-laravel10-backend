<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Tbl_retailer extends Model
{
    use HasFactory;

	protected $table = 'tbl_retailer';
	protected $primaryKey = "retailer_id";
    public $timestamps = false;
}
