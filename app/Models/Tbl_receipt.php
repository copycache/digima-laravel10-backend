<?php
namespace App\Models;
 use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
 class Tbl_receipt extends Model
{
    use HasFactory;

	protected $guarded = [];

	protected $table = 'tbl_receipt';
	protected $primaryKey = "receipt_id";
    public $timestamps = false;
} 
