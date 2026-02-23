<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Tbl_marketing_tools_subcategory extends Model
{
    use HasFactory;

	protected $table = 'tbl_marketing_tools_subcategory';

	protected $primaryKey = "id";

	protected $guarded = [];
    public $timestamps = false;
}
