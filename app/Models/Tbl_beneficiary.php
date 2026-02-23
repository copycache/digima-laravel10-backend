<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Tbl_beneficiary extends Model
{
    use HasFactory;

	protected $table = 'tbl_beneficiary';

	protected $primaryKey = "id";
    public $timestamps = false;

	protected $guarded = [];
}
