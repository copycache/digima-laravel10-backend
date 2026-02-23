<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Tbl_dynamic_compression_record extends Model
{
    use HasFactory;

	protected $primaryKey = "id";

	protected $table = 'tbl_dynamic_compression_record';
    public $timestamps = false;

	protected $guarded = [];
    protected $primarykey = 'id'; 
}
