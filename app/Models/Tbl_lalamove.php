<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Tbl_lalamove extends Model
{
    use HasFactory;

	protected $table = 'tbl_lalamove';

	protected $primaryKey = "id";

	protected $guarded = [];
    public $timestamps = false;
}

