<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Tbl_lockdown_autoship_items extends Model
{
    use HasFactory;

	protected $table = 'tbl_lockdown_autoship_items';
	protected $primaryKey = "lockdown_autoship_items_id";
    public $timestamps = false;
}
