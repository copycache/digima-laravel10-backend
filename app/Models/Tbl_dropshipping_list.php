<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Tbl_dropshipping_list extends Model
{
    use HasFactory;

	protected $table = 'tbl_dropshipping_list';
	protected $primaryKey = "id";
    public $timestamps = false;
}
