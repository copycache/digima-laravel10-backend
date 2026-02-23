<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Tbl_audit_trail extends Model
{
    use HasFactory;

	protected $guarded = [];

	protected $table = 'tbl_audit_trail';
	protected $primaryKey = "audit_trail_id";
    public $timestamps = false;
    
    
    
}
