<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Tbl_company_settings extends Model
{
    use HasFactory;

	protected $table = 'tbl_company_settings';
	protected $primaryKey = "company_settings_id";
    public $timestamps = false;

	protected $guarded = [];

}
