<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Tbl_survey_settings extends Model
{
    use HasFactory;

protected $table = 'tbl_survey_settings';
protected $primaryKey = "id";
protected $guarded = [];
public $timestamps = false;
}
