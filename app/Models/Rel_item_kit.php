<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Rel_item_kit extends Model
{
    use HasFactory;

	protected $primaryKey = "id";

	protected $table = 'rel_item_kit';
    public $timestamps = false;

	protected $guarded = [];
}
