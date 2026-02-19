<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Tbl_cart extends Model
{
    use HasFactory;

protected $table = 'tbl_cart';
protected $primaryKey = "cart_id";
    public $timestamps = false;
}
