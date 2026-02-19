<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Tbl_item extends Model
{
    use HasFactory;

protected $table = 'tbl_item';
protected $primaryKey = "item_id";
    public $timestamps = false;

    public function scopeUnarchived($query)
    {
    return $query->where("tbl_item.archived", 0);
    }

public function scopeJoinInventory($query)
    {
    return $query->join('tbl_inventory', 'tbl_item.item_id', '=', 'tbl_inventory.inventory_item_id');
    }

    public function scopeJoinBranch($query)
    {
    return $query->join('tbl_branch', 'tbl_inventory.inventory_branch_id', '=', 'tbl_branch.branch_id')->where('tbl_branch.archived', 0);
    }

    public function scopeJoinCodesInventory($query)
    {
    return $query->join('tbl_codes', 'tbl_inventory.inventory_id', '=', 'tbl_codes.code_inventory_id');
    }

    public function scopeSold($query)
    {
    return $query->where('tbl_codes.code_sold', 1);
    }

    public function scopeUsed($query)
    {
    return $query->where('tbl_codes.code_used', 1);
    }
}
