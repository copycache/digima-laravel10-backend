<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Tbl_mlm_board_placement extends Model
{
    use HasFactory;

    protected $table = 'tbl_mlm_board_placement';
    protected $primaryKey = "mlm_board_placement_id";
    public $timestamps = false;
}
