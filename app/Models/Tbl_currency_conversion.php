<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Tbl_currency_conversion extends Model
{
    use HasFactory;

	protected $table = 'tbl_currency_conversion';
	protected $primaryKey = "currency_conversion_id";
    public $timestamps = false;

	protected $guarded = [];

}

