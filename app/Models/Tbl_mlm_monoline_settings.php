<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Tbl_mlm_monoline_settings extends Model
{
    use HasFactory;

	protected $table = 'tbl_mlm_monoline_settings';
	protected $primaryKey = "monoline_settings_id";
    public $timestamps = false;
}
