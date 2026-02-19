<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Tbl_slot_transfer extends Model
{
    use HasFactory;

	protected $table = 'tbl_slot_transfer';
	protected $primaryKey = "slot_transfer_id";
    public $timestamps = false;
}
