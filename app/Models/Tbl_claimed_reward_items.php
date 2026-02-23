<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Tbl_claimed_reward_items extends Model
{
    use HasFactory;

    protected $table = 'tbl_claimed_reward_items';

    protected $primaryKey = "id";
    public $timestamps = false;

    protected $guarded = [];

}
