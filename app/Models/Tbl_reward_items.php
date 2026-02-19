<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Tbl_reward_items extends Model
{
    use HasFactory;

    protected $table = 'tbl_reward_items';
	protected $primaryKey = "id";
    public $timestamps = false;

    public function currency()
    {
        return $this->belongsTo(Tbl_currency::class, 'currency_id', 'currency_id');
    }

    public function membership()
    {
        return $this->belongsTo(Tbl_membership::class, 'membership_id', 'membership_id');
    }
}
