<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
class Tbl_passive_unilevel_premium extends Model
{
    use HasFactory;

	protected $guarded = [];

	protected $table = 'tbl_passive_unilevel_premium';
	protected $primaryKey = "premium_id";
    public $timestamps = false;
}
