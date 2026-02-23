<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Tbl_island_group extends Model
{
    use HasFactory;

	protected $table = 'tbl_island_group';

	protected $primaryKey = "id";
    public $timestamps = false;

	protected $guarded = [];

}

