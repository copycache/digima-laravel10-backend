<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Tbl_investment_package extends Model
{
    use HasFactory;

	protected $table = 'tbl_investment_package';
	protected $primaryKey = "investment_package_id";
    public $timestamps = false;
}

