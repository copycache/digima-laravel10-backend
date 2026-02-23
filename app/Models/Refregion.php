<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Refregion extends Model
{
    use HasFactory;

	protected $table = 'refregion';

	protected $primaryKey = "id";
    public $timestamps = false;

	protected $guarded = [];
}
