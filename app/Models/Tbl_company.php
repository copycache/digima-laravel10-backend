<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Tbl_company extends Model
{
    use HasFactory;

	protected $table = 'tbl_company';
	protected $primaryKey = "company_id";
    public $timestamps = false;

	protected $guarded = [];

}
