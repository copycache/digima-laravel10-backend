<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Tbl_cod_list extends Model
{
    use HasFactory;

	protected $table = 'tbl_cod_list';
	protected $primaryKey = "id";
    public $timestamps = false;
}
