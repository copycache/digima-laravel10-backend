<?php
namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tbl_orders extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $table = 'tbl_orders';

    protected $primaryKey = 'order_id';

    public $timestamps = false;

    public function scopeGetWeekOrder($query)
    {
        $query
            ->where('order_date_created', '>', Carbon::now()->startOfWeek())
            ->where('order_date_created', '<', Carbon::now()->endOfWeek());

        return $query;
    }

    public function items()
    {
        return $this->belongsToMany(Tbl_item::class, 'tbl_orders_rel_item', 'rel_order_id', 'item_id');
    }

    public function receipt()
    {
        return $this->hasOne(Tbl_receipt::class, 'receipt_order_id', 'order_id');
    }
}
