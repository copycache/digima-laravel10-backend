<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Tbl_dealer extends Model
{
    use HasFactory;

	protected $table = 'tbl_dealer';
	protected $primaryKey = "dealer_id";
    public $timestamps = false;

	protected $guarded = [];

}
