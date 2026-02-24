<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Tbl_ai_marketing_tools extends Model
{
    use HasFactory;

	protected $table = 'tbl_ai_marketing_tools';

	protected $primaryKey = "id";
    public $timestamps = false;
    protected $guarded = [];

	public function scopeCategory($query)
    {
    	return $query->leftJoin('tbl_marketing_tools_category', 'tbl_marketing_tools_category.id', '=', 'tbl_ai_marketing_tools.category');
    }

    public function scopeSubCategory($query)
    {
    	return $query->leftJoin('tbl_marketing_tools_subcategory', 'tbl_marketing_tools_subcategory.id', '=', 'tbl_ai_marketing_tools.sub_category');
    }
}
