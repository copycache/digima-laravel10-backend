<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Tbl_currency extends Model
{
    use HasFactory;

protected $table = 'tbl_currency';
protected $primaryKey = "currency_id";
    public $timestamps = false;

    public function scopeWallet($query)
    {
    $query ->join('tbl_wallet','tbl_wallet.currency_id','=','tbl_currency.currency_id');

    return $query;
    }
}
