<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Tbl_earning_log extends Model
{
    use HasFactory;

protected $table = 'tbl_earning_log';
protected $primaryKey = "earning_log_id";
public $timestamps = false;
protected $guarded = [];

}
