<?php
namespace App\Http\Controllers\Admin;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Tbl_slot;
use Illuminate\Support\Facades\Request;

class AdminNegativeWalletController extends AdminController
{
    public static function show_negative_wallet()
    {
        $return = Tbl_slot::Owner()
                            ->JoinWallet()
                            ->JoinCurrency()
                            ->where('wallet_amount','<',0)
                            ->select('name','tbl_slot.slot_id','slot_no','currency_abbreviation','wallet_amount')
                            ->get();
        echo "<table>";
        echo "<tr>";
        echo "<th>Name</th>";
        echo "<th>Slot Code</th>";
        echo "<th>Amount</th>";
        echo "</tr>";
        foreach ($return as $key => $value) 
        {   
            $rounded = round($value->wallet_amount,2);
            echo "<tr>";
            echo "<td>$value->name</td>";
            echo "<td>$value->slot_no</td>";
            echo "<td>$value->currency_abbreviation "." $rounded</td>";
            echo "</tr>";
        }
        echo "</table>";

    }
}
