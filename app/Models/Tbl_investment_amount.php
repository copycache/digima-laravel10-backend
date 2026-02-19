<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Tbl_investment_amount extends Model
{
    use HasFactory;

	protected $table = 'tbl_investment_amount';
	protected $primaryKey = "id";
    public $timestamps = false;
}

