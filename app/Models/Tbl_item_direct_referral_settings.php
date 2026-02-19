<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Tbl_item_direct_referral_settings extends Model
{
    use HasFactory;

	protected $table = 'tbl_item_direct_referral_settings';
	protected $primaryKey = "id";
    public $timestamps = false;
}
