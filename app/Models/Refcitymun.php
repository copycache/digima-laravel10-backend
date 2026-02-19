<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Refcitymun extends Model
{
    use HasFactory;

	protected $table = 'refcitymun';
	protected $primaryKey = "id";
    public $timestamps = false;
}
