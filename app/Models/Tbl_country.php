<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Tbl_country extends Model
{
    use HasFactory;

	protected $table = 'tbl_country';
	protected $primaryKey = "country_id";
    public $timestamps = false;

	protected $guarded = [];

}
