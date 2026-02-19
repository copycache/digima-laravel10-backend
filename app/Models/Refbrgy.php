<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Refbrgy extends Model
{
    use HasFactory;

	protected $table = 'refbrgy';
	protected $primaryKey = "id";
    public $timestamps = false;
}
