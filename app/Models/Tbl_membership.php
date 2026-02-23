<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Tbl_membership extends Model
{
    use HasFactory;

protected $guarded = [];

protected $table = 'tbl_membership';
protected $primaryKey = "membership_id";
    public $timestamps = false;


    public function scopeItemDiscount($query)
    {
    $query ->join('tbl_item_membership_discount','tbl_item_membership_discount.membership_id','=','tbl_membership.membership_id');
    return $query;
    }
}
