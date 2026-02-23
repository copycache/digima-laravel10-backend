<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Tbl_ebooks extends Model
{
    use HasFactory;

	protected $table = 'tbl_ebooks';

	protected $primaryKey = "id";
    public $timestamps = false;

	protected $guarded = [];

}
