<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Tbl_welcome_bonus_commissions extends Model
{
    use HasFactory;

	protected $table = 'tbl_welcome_bonus_commissions';

	protected $primaryKey = "id";

	protected $guarded = [];
    public $timestamps = false;
}
