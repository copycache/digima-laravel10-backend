<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Tbl_survey_question extends Model
{
    use HasFactory;

	protected $table = 'tbl_survey_question';
	protected $primaryKey = "id";
    public $timestamps = false;
}
