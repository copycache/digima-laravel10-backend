<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Tbl_code_alias extends Model
{
    use HasFactory;

	protected $table = 'tbl_code_alias';
    public $timestamps = false;

	protected $guarded = [];

	protected $primaryKey = "id";

}
