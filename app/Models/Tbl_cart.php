<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tbl_cart extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $table = 'tbl_cart';

    protected $primaryKey = 'cart_id';

    public $timestamps = false;
}
