<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Tbl_wallet extends Model
{
    use HasFactory;

protected $table = 'tbl_wallet';
protected $primaryKey = "wallet_id";
    public $timestamps = false;

    public function scopeCurrency($query)
    {
       $query->join('tbl_currency', 'tbl_currency.currency_id', '=', 'tbl_wallet.currency_id');
        return $query;
    }

    public function scopePeso($query)
    {
    return $query->where('tbl_currency.currency_id', 1);
    }
}
