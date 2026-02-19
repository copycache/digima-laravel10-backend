<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Tbl_payout_method extends Model
{
    use HasFactory;

	protected $table = 'tbl_payout_method';
	protected $primaryKey = "payout_method_id";
    public $timestamps = false;
}
