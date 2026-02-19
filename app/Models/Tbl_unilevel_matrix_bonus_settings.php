<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Tbl_unilevel_matrix_bonus_settings extends Model
{
    use HasFactory;

	protected $table = 'tbl_unilevel_matrix_bonus_settings';
	protected $primaryKey = "id";
    public $timestamps = false;
}
