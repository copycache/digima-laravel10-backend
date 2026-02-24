<?php
namespace App\Http\Controllers\Member;

use App\Globals\Investment;
use App\Globals\Log;
use App\Globals\MLM;
use App\Models\Tbl_currency;
use App\Models\Tbl_investment_amount;
use App\Models\Tbl_investment_package;
use App\Models\Tbl_investment_package_logs;
use App\Models\Tbl_investment_package_tag;
use App\Models\Tbl_slot;
use App\Models\Tbl_wallet;
use Carbon\Carbon;
use Illuminate\Support\Facades\Request;

class MemberInvestMentController extends MemberController
{
    public static function wallet_balance($slot_id)
    {
        $currency_default = Tbl_currency::where('currency_default', 1)->first();
        $wallet_log = Tbl_wallet::where('slot_id', $slot_id)->where('currency_id', $currency_default->currency_id)->first();

        $balance = $wallet_log->wallet_amount == null ? 0 : $wallet_log->wallet_amount;
        return $balance;
    }

    public function get_package_list()
    {
        $slot_id = Request::input('slot_id');
        $membership = Tbl_slot::where('slot_id', $slot_id)->first()->slot_membership ?? 0;
        $response = Tbl_investment_package::where(function ($response) use ($membership) {
            $response
                ->where('bind_membership', $membership)
                ->orWhere('bind_membership', 0);
        })->where('archive', 0)->get();

        foreach ($response as $key => $res) {
            $month_bond = round($res->investment_package_days_bond / 30) <= 1 ? 'month' : 'months';
            $day_bond = $res->investment_package_days_bond == 1 ? 'day' : 'days';
            $day_margin = $res->investment_package_days_margin == 1 ? 'day' : 'days';

            $response[$key]['months'] = round($res->investment_package_days_bond / 30) . ' ' . $month_bond;
            $response[$key]['days'] = $res->investment_package_days_bond . ' ' . $day_bond;

            if ($res->investment_package_min_interest != $res->investment_package_max_interest) {
                $response[$key]['description'] = $res->investment_package_min_interest . ' % - ' . $res->investment_package_max_interest . ' % Interest in every ' . $res->investment_package_days_margin . ' ' . $day_margin;
            } else {
                $response[$key]['description'] = $res->investment_package_min_interest . ' %  Interest in every ' . $res->investment_package_days_margin . ' ' . $day_margin;
            }
        }
        return response()->json($response, 200);
    }

    public function investment_preview()
    {
        $investment_package_id = Request::input('investment_package_id');
        $investment_amount = Request::input('investment_amount');
        $package = Tbl_investment_package::where('investment_package_id', $investment_package_id)->first();

        $min_interest_amount = 0;
        $max_interest_amount = 0;
        $volume = 20;
        $date = date('Y-m-d');
        $new_date = date('Y-m-d', strtotime('+' . $package->investment_package_days_bond . ' days', strtotime($date)));
        $array = array();

        $get_days = $package->investment_package_days_bond / $package->investment_package_days_margin;

        for ($start = 0; $start < $get_days; $start++) {
            $insert['date_cut_off'] = $date = date('Y-m-d', strtotime('+' . $package->investment_package_days_margin . ' days', strtotime($date)));
            $insert['min_interest_amount'] = $min = $investment_amount * ($package->investment_package_min_interest / 100);
            $insert['max_interest_amount'] = $max = $investment_amount * ($package->investment_package_max_interest / 100);
            $insert['date_format'] = date('F j, Y', strtotime($date));

            $min_interest_amount = $min_interest_amount + $min;
            $max_interest_amount = $max_interest_amount + $max;
            array_push($array, $insert);
        }
        $package->min_interest_amount = $min_interest_amount;
        $package->max_interest_amount = $max_interest_amount;
        $package->investment_amount = $investment_amount;
        $package->cut_off = $array;

        $min_max = $package->investment_package_min_interest == $package->investment_package_max_interest ? $package->investment_package_max_interest . ' %' : $package->investment_package_min_interest . ' % - ' . $package->investment_package_max_interest . ' %';
        $package->message = "You're about to invest " . number_format($investment_amount, 2) . ' within ' . $package->investment_package_days_bond . ' day(s) (' . round($package->investment_package_days_bond / 30) . ' month(s)) period with the interest of ' . $min_max . ' in every ' . $package->investment_package_days_margin . ' day(s)';

        return response()->json($package, 200);
    }

    public function investment_submit()
    {
        $balance = Self::wallet_balance(Request::input('slot_id'));
        $invest_amount = Tbl_investment_amount::first();

        if (Request::input('investment_amount') < 0) {
            $message['message'] = 'Please Invest a proper amount';
            $message['alert'] = 'Failed';
            return response()->json($message, 200);
        } else {
            if ($balance < Request::input('investment_amount')) {
                $message['message'] = 'You dont have enough balance';
                $message['alert'] = 'Failed';
                return response()->json($message, 200);
            } else {
                if (Request::input('investment_amount') < $invest_amount->min_amount) {
                    $message['message'] = 'Minimum Investment is Php ' . number_format($invest_amount->min_amount, 2);
                    $message['alert'] = 'Failed';
                    return response()->json($message, 200);
                } else {
                    if (Request::input('investment_amount') > $invest_amount->max_amount) {
                        $message['message'] = 'Maximum Investment is Php ' . number_format($invest_amount->max_amount, 2);
                        $message['alert'] = 'Failed';
                        return response()->json($message, 200);
                    } else {
                        $insert['investment_amount'] = Request::input('investment_amount');
                        $insert['investment_date'] = Carbon::now();
                        $insert['investment_package_id'] = Request::input('investment_package_id');
                        $insert['slot_id'] = Request::input('slot_id');
                        $insert['user_id'] = Request::user()->id;
                        $investment_package_tag_id = Tbl_investment_package_tag::insertGetId($insert);

                        $package = Tbl_investment_package_tag::where('investment_package_tag_id', $investment_package_tag_id)->Package()->first();
                        $date = date('Y-m-d');
                        $get_days = $package->investment_package_days_bond / $package->investment_package_days_margin;

                        for ($start = 0; $start < $get_days; $start++) {
                            $time = Carbon::now()->format('H:i:s');
                            $investment_package_logs_date = date('Y-m-d' . ' ' . $time, strtotime('+' . $package->investment_package_days_margin . ' days', strtotime($date)));
                            $logs['investment_package_logs_date'] = $investment_package_logs_date;
                            $logs['investment_package_logs_amount'] = '0';
                            $logs['investment_package_tag_id'] = $investment_package_tag_id;
                            $date = $investment_package_logs_date;
                            Tbl_investment_package_logs::insert($logs);
                        }
                        Log::insert_wallet($package->slot_id, '-' . $package->investment_amount, 'INVESTMENT');
                        $message['message'] = 'Investment Successfully Added!';
                        $message['alert'] = 'Success';
                        return response()->json($message, 200);
                    }
                }
            }
        }
    }

    public function get_investment_list()
    {
        Investment::load_package();

        $slot_id = Request::input('slot_id');
        $investment_list = Tbl_investment_package_tag::where('user_id', Request::user()->id)
            ->where('slot_id', $slot_id)
            ->leftjoin('tbl_investment_package', 'tbl_investment_package.investment_package_id', '=', 'tbl_investment_package_tag.investment_package_id')
            ->get();
        $investment = count($investment_list);
        $interest_amount = 0;
        $principal_amount = 0;
        $volume = 20;

        $tag_ids = $investment_list->pluck('investment_package_tag_id');
        $all_logs = Tbl_investment_package_logs::whereIn('investment_package_tag_id', $tag_ids)
            ->orderBy('investment_package_logs_date', 'DESC')
            ->get()
            ->groupBy('investment_package_tag_id');

        foreach ($investment_list as $key => $list) {
            $logs = $all_logs->get($list->investment_package_tag_id, collect());
            $last_log_entry = $logs->first();
            $last_logs = $last_log_entry ? $last_log_entry->investment_package_logs_date : null;

            $investment_list[$key]['investment_interest_amount'] = $interest = $logs->sum('investment_package_logs_amount');
            $investment_list[$key]['investment_date_format'] = date('F j, Y', strtotime($list->investment_date));
            $investment_list[$key]['interest'] = $list->investment_package_min_interest == $list->investment_package_max_interest ? $list->investment_package_max_interest . '%' : $list->investment_package_min_interest . '% - ' . $list->investment_package_max_interest . '%';

            if ($last_logs) {
                $last_log_time = date($last_logs);
                $current_log = strtotime($last_logs) <= time() ? $last_logs : date('Y-m-d H:i:s');
                $investment_list[$key]['remaining_days'] = date_create($current_log)->diff(date_create($last_log_time))->format('%a day(s) %h hrs %i min');
            } else {
                $investment_list[$key]['remaining_days'] = 'N/A';
            }

            $interest_amount = $interest_amount + $interest;
            $principal_amount = $principal_amount + $list->investment_amount;
        }
        $return['wallet_balance'] = Self::wallet_balance($slot_id);
        $return['investment_list'] = $investment_list;
        $return['investment'] = $investment;
        $return['total_interest'] = $interest_amount;
        $return['total_principal'] = $principal_amount;

        return response()->json($return, 200);
    }

    public function investment_details()
    {
        $return = Tbl_investment_package_logs::where('investment_package_tag_id', Request::input('investment_package_tag_id'))->get();
        foreach ($return as $key => $returns) {
            $return[$key]['date_format'] = date('F j, Y', strtotime($returns->investment_package_logs_date));
        }
        return response()->json($return, 200);
    }

    public function get_minimum_amount()
    {
        $response = Tbl_investment_amount::first();
        return $response;
    }
}
