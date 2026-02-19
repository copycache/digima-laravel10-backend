<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Tbl_achievers_rank extends Model
{
    use HasFactory;

	// Created By: Centy - 10-27-2023
	protected $table = 'tbl_achievers_rank';
	protected $primaryKey = "achievers_rank_id";
    public $timestamps = false;
}
