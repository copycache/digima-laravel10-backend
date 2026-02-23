<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Tbl_incentive_items extends Model
{
    use HasFactory;

    protected $table = 'tbl_incentive_items';
	protected $primaryKey = "item_id";
    public $timestamps = false;

    protected $guarded = [];

}
