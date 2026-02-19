<?php
namespace App\Globals;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

use App\Models\Tbl_audit_trail;

class Audit_trail
{
    public static function audit($old = null,$new = null,$user = null,$action = null)
    {
        $insert['user_id']        = $user;
        $insert['action']         = $action;
        $insert['old_value']      = $old;
        $insert['new_value']      = $new;
        $insert['date_created']   = Carbon::now();

        Tbl_audit_trail::insert($insert);
    }
}
