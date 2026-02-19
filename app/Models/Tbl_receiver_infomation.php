<?php
namespace App\Models;
 use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
 class Tbl_receiver_infomation extends Model
{
    use HasFactory;

	protected $table = 'tbl_receiver_infomation';
	protected $primaryKey = "id";
    public $timestamps = false;
} 
