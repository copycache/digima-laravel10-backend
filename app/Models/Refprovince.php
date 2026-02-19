<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Refprovince extends Model
{
    use HasFactory;

	protected $table = 'refprovince';
	protected $primaryKey = "id";
    public $timestamps = false;
}
