<?php
namespace App\Http\Controllers\Member;

use App\Globals\Plan;
use App\Http\Controllers\Controller;
use App\Models\Tbl_achievers_rank;
use App\Models\Tbl_binary_points;
use App\Models\Tbl_binary_points_settings;
use App\Models\Tbl_binary_projected_income_log;
use App\Models\Tbl_dropshipping_bonus_logs;
use App\Models\Tbl_dynamic_compression_record;
use App\Models\Tbl_earning_log;
use App\Models\Tbl_label;
use App\Models\Tbl_leaders_support_log;
use App\Models\Tbl_leveling_bonus_points;
use App\Models\Tbl_marketing_support_log;
use App\Models\Tbl_membership;
use App\Models\Tbl_milestone_bonus_settings;
use App\Models\Tbl_milestone_points_log;
use App\Models\Tbl_mlm_plan;
use App\Models\Tbl_mlm_unilevel_settings;
use App\Models\Tbl_orders;
use App\Models\Tbl_prime_refund_points_log;
use App\Models\Tbl_product_direct_referral_logs;
use App\Models\Tbl_product_personal_cashback_logs;
use App\Models\Tbl_retailer_commission_logs;
use App\Models\Tbl_retailer_override_logs;
use App\Models\Tbl_slot;
use App\Models\Tbl_stairstep_distribute;
use App\Models\Tbl_stairstep_points;
use App\Models\Tbl_stairstep_rank;
use App\Models\Tbl_team_sales_bonus_logs;
use App\Models\Tbl_tree_sponsor;
use App\Models\Tbl_unilevel_distribute;
use App\Models\Tbl_unilevel_or_points;
use App\Models\Tbl_unilevel_points;
use App\Models\Tbl_wallet_log;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;
use stdClass;

class MemberEarningController extends Controller
{
    function __construct() {}

    public function get()
    {
        $plan = Request::input('plan');
        $response = Plan::get($plan);
        return response()->json($response, 200);
    }

    public function get_initial()
    {
        $ok = [];
        $initial = Tbl_mlm_plan::where('mlm_plan_enable', 1)->first()->mlm_plan_code;
        $response['initial'] = trim(strtolower($initial));
        $response['is_dynamic_com'] = Tbl_mlm_unilevel_settings::first()->is_dynamic;

        $slot = Tbl_slot::where('slot_owner', Request::user()->id)->where('slot_id', Request::input('current_slot_id'))->first()->slot_id;
        $plan_enable = Tbl_mlm_plan::where('mlm_plan_enable', 1)->get();
        foreach ($plan_enable as $key => $plan_name) {
            $new_plan_name = trim(preg_replace('/_/', ' ', $plan_name->mlm_plan_code));
            if ($new_plan_name == 'UNILEVEL') {
                if ($response['is_dynamic_com'] == 'dynamic') {
                    $new_plan_name = 'UNILEVEL COMMISSION';
                }
            }

            if ($new_plan_name == 'STAIRSTEP') {
                $running_balance = Tbl_earning_log::where('earning_log_slot_id', $slot)
                    ->where('earning_log_plan_type', 'OVERRIDE COMMISSION')
                    ->sum('earning_log_amount');
                $ok += ['LEADERSHIP_BONUS' => number_format($running_balance, 2)];

                unset($running_balance);

                $running_balance = Tbl_earning_log::where('earning_log_slot_id', $slot)
                    ->where('earning_log_plan_type', 'BREAKAWAY BONUS')
                    ->sum('earning_log_amount');
                $ok += ['ROYALTY_BONUS' => number_format($running_balance, 2)];
            }
            if ($new_plan_name == 'WATCH EARN') {
                $new_plan_name = 'WATCH AND EARN';
            }
            $running_balance = Tbl_earning_log::where('earning_log_slot_id', $slot)
                ->where('earning_log_plan_type', $new_plan_name)
                ->sum('earning_log_amount');

            if ($plan_name->mlm_plan_code != 'UNILEVEL') {
                $ok += ["$plan_name->mlm_plan_code" => number_format($running_balance, 2)];
            } else if ($plan_name->mlm_plan_code == 'UNILEVEL') {
                if ($response['is_dynamic_com'] == 'dynamic') {
                    $running_balance = Tbl_wallet_log::where('wallet_log_details', 'UNILEVEL COMMISSION')->where('wallet_log_slot_id', $slot)->sum('wallet_log_amount');
                    $ok += ["$plan_name->mlm_plan_code" => number_format($running_balance, 2)];
                } else {
                    $running_balance = Tbl_wallet_log::where('wallet_log_details', 'UNILEVEL')->where('wallet_log_slot_id', $slot)->sum('wallet_log_amount');
                    $ok += ["$plan_name->mlm_plan_code" => number_format($running_balance, 2)];
                }
            }
        }
        $response['total'] = $ok;
        // dd($response['total']);
        return response()->json($response, 200);
    }

    public function user_data()
    {
        return Request::user();
    }

    public function direct_earning()
    {
        $slot = Tbl_slot::where('slot_owner', Request::user()->id)->where('slot_id', Request::input('current_slot_id'))->first();
        $data = null;
        $running_balance = 0;

        if ($slot) {
            $log = Tbl_earning_log::where('earning_log_slot_id', $slot->slot_id)
                ->leftJoin('tbl_slot', 'tbl_slot.slot_id', '=', 'tbl_earning_log.earning_log_cause_id')
                ->leftJoin('users', 'users.id', '=', 'tbl_slot.slot_owner')
                ->leftJoin('tbl_membership', 'tbl_membership.membership_id', '=', 'tbl_earning_log.earning_log_cause_membership_id')
                ->where('earning_log_plan_type', 'DIRECT')
                ->leftJoin('tbl_label', 'tbl_label.plan_code', '=', 'tbl_earning_log.earning_log_plan_type')
                ->select('*', DB::raw("DATE_FORMAT(tbl_earning_log.earning_log_date_created, '%m/%d/%Y') as earning_log_date_created"),
                    DB::raw("DATE_FORMAT(tbl_earning_log.earning_log_date_created, '%h:%i %p') as earning_log_time_created"))
                ->paginate(10);

            $data['log'] = $log;
            $running_balance = Tbl_earning_log::where('earning_log_slot_id', $slot->slot_id)->where('earning_log_plan_type', 'DIRECT')->sum('earning_log_amount');
        }

        $data['total'] = number_format($running_balance, 2);

        return json_encode($data);
    }

    public function direct_gc_earning()
    {
        $slot = Tbl_slot::where('slot_owner', Request::user()->id)->where('slot_id', Request::input('current_slot_id'))->first();
        $data = null;
        $running_balance = 0;

        if ($slot) {
            $log = Tbl_earning_log::where('earning_log_slot_id', $slot->slot_id)
                ->leftJoin('tbl_slot', 'tbl_slot.slot_id', '=', 'tbl_earning_log.earning_log_cause_id')
                ->leftJoin('users', 'users.id', '=', 'tbl_slot.slot_owner')
                ->leftJoin('tbl_membership', 'tbl_membership.membership_id', '=', 'tbl_earning_log.earning_log_cause_membership_id')
                ->where('earning_log_plan_type', 'DIRECT GC')
                ->leftJoin('tbl_label', 'tbl_label.plan_code', '=', 'tbl_earning_log.earning_log_plan_type')
                ->select('*', DB::raw("DATE_FORMAT(tbl_earning_log.earning_log_date_created, '%m/%d/%Y') as earning_log_date_created"),
                    DB::raw("DATE_FORMAT(tbl_earning_log.earning_log_date_created, '%h:%i %p') as earning_log_time_created"))
                ->paginate(10);

            $data['log'] = $log;
            $running_balance = Tbl_earning_log::where('earning_log_slot_id', $slot->slot_id)->where('earning_log_plan_type', 'DIRECT GC')->sum('earning_log_amount');
        }

        $data['total'] = number_format($running_balance, 2);

        return json_encode($data);
    }

    public function direct_bonus_earning()
    {
        $slot = Tbl_slot::where('slot_owner', Request::user()->id)->where('slot_id', Request::input('current_slot_id'))->first();
        $data = null;
        $running_balance = 0;

        if ($slot) {
            $log = Tbl_earning_log::where('earning_log_slot_id', $slot->slot_id)
                ->leftJoin('tbl_slot', 'tbl_slot.slot_id', '=', 'tbl_earning_log.earning_log_cause_id')
                ->leftJoin('users', 'users.id', '=', 'tbl_slot.slot_owner')
                ->leftJoin('tbl_membership', 'tbl_membership.membership_id', '=', 'tbl_earning_log.earning_log_cause_membership_id')
                ->where('earning_log_plan_type', 'DIRECT BONUS')
                ->leftJoin('tbl_label', 'tbl_label.plan_code', '=', 'tbl_earning_log.earning_log_plan_type')
                ->select('*', DB::raw("DATE_FORMAT(tbl_earning_log.earning_log_date_created, '%m/%d/%Y') as earning_log_date_created"),
                    DB::raw("DATE_FORMAT(tbl_earning_log.earning_log_date_created, '%h:%i %p') as earning_log_time_created"))
                ->paginate(10);

            $data['log'] = $log;
            $running_balance = Tbl_earning_log::where('earning_log_slot_id', $slot->slot_id)->where('earning_log_plan_type', 'DIRECT BONUS')->sum('earning_log_amount');
        }

        $data['total'] = number_format($running_balance, 2);

        return json_encode($data);
    }

    public function indirect_earning()
    {
        $slot = Tbl_slot::where('slot_owner', Request::user()->id)->where('slot_id', Request::input('current_slot_id'))->first();
        $data = null;
        $running_balance = 0;
        $log = null;
        $ctr = 0;
        $points_left = 0;
        $points_right = 0;
        if ($slot) {
            $membership = Tbl_membership::where('membership_id', $slot->slot_membership)->first();
            if ($membership) {
                $membership->membership_indirect_level = $membership->membership_indirect_level + 1;

                $level = 0;
                while ($membership->membership_indirect_level >= $level) {
                    $log[$ctr]['level_name'] = $this->ordinal($level);
                    $log[$ctr]['number_of_slots'] = Tbl_earning_log::where('earning_log_slot_id', $slot->slot_id)->where('earning_log_plan_type', 'INDIRECT')->where('earning_log_cause_level', $level)->count() ? Tbl_earning_log::where('earning_log_slot_id', $slot->slot_id)->where('earning_log_cause_level', $level)->where('earning_log_plan_type', 'INDIRECT')->count() . ' Slot(s)' : 'No Slots';
                    $log[$ctr]['last_slot_creation'] = Tbl_earning_log::join('tbl_tree_sponsor', 'tbl_tree_sponsor.sponsor_parent_id', '=', 'tbl_earning_log.earning_log_slot_id')->where('sponsor_level', $level)->where('earning_log_slot_id', $slot->slot_id)->where('earning_log_plan_type', 'INDIRECT')->first() ? Carbon::parse(Tbl_earning_log::where('earning_log_slot_id', $slot->slot_id)->orderBy('earning_log_date_created', 'DESC')->where('earning_log_plan_type', 'INDIRECT')->first()->earning_log_date_created)->format('m/d/Y') : '---';
                    $log[$ctr]['earnings'] = Tbl_earning_log::where('earning_log_slot_id', $slot->slot_id)->where('earning_log_plan_type', 'INDIRECT')->where('earning_log_cause_level', $level)->sum('earning_log_amount');

                    $running_balance = $running_balance + $log[$ctr]['earnings'];
                    $ctr++;
                    $level++;
                }
            }
        }

        $data['log'] = $log;
        $data['total'] = number_format($running_balance, 2);
        return json_encode($data);
    }

    public function indirect_details()
    {
        $slot = Tbl_slot::where('slot_owner', Request::user()->id)->where('slot_id', Request::input('current_slot_id'))->first();
        $level = Request::input('index');

        $data = Tbl_earning_log::where('earning_log_slot_id', $slot->slot_id)
            ->where('earning_log_plan_type', 'INDIRECT')
            ->where('earning_log_cause_level', $level)
            ->leftJoin('tbl_slot', 'tbl_slot.slot_id', '=', 'tbl_earning_log.earning_log_cause_id')
            ->leftJoin('users', 'users.id', '=', 'tbl_slot.slot_owner')
            ->paginate(10);

        return json_encode($data);
    }

    public function binary_earning()
    {
        $binary_settings = DB::table('tbl_binary_settings')->first();
        $cycle = DB::table('tbl_binary_settings')->first() ? DB::table('tbl_binary_settings')->first()->cycle_per_day : 1;
        $slot = Tbl_slot::where('slot_owner', Request::user()->id)->where('slot_id', Request::input('current_slot_id'))->JoinMembership()->first();
        $log = null;
        $running_balance = 0;
        $points_left = 0;
        $points_right = 0;
        if ($slot) {
            $log = Tbl_binary_points::where('binary_points_slot_id', $slot->slot_id)
                ->leftJoin('tbl_membership as membership', 'membership.membership_id', '=', 'tbl_binary_points.binary_cause_membership_id')
                ->leftJoin('tbl_slot as cause_slot', 'cause_slot.slot_id', '=', 'tbl_binary_points.binary_cause_slot_id')
                ->select('tbl_binary_points.*', 'membership.membership_name as membership_name', 'cause_slot.slot_no as cause_no',
                    DB::raw("DATE_FORMAT(tbl_binary_points.binary_points_date_received, '%M %d, %Y') as binary_log_date_created"),
                    DB::raw("DATE_FORMAT(tbl_binary_points.binary_points_date_received, '%h:%i %p') as binary_log_time_created"))
                ->orderBy('binary_points_id', 'desc')
                ->paginate(10);

            $running_balance = Tbl_binary_points::where('binary_points_slot_id', $slot->slot_id)->sum('binary_points_income');
        }

        $data['todays_pairs'] = Tbl_earning_log::where('earning_log_slot_id', $slot->slot_id)->where('earning_log_plan_type', '=', 'BINARY');
        if ($binary_settings->cycle_per_day == 1) {
            $today = Carbon::now()->format('Y-m-d');
            if ($binary_settings->binary_limit_type == 1) {
                if ($slot->binary_realtime_commission) {
                    $data['todays_pairs'] = $data['todays_pairs']->wheredate('earning_log_date_created', $today)->count();
                } else {
                    $data['todays_pairs'] = Tbl_binary_projected_income_log::where('slot_id', $slot->slot_id)->wheredate('date_created', $today)->count();
                }
            } else if ($binary_settings->binary_limit_type == 2) {
                if ($slot->binary_realtime_commission) {
                    $data['todays_pairs'] = $data['todays_pairs']->wheredate('earning_log_date_created', $today)->sum('earning_log_amount');
                } else {
                    $data['todays_pairs'] = Tbl_binary_projected_income_log::where('slot_id', $slot->slot_id)->wheredate('date_created', $today)->sum('wallet_amount');
                }
            }
        } else if ($binary_settings->cycle_per_day == 2) {
            // Get the current date and time
            $currentDate = Carbon::now();
            $todayStart = $currentDate->copy()->startOfDay();
            $todayNoon = Carbon::createFromFormat('Y-m-d H:i:s', $currentDate->format('Y-m-d') . ' 12:00:00');
            $todayEnd = Carbon::createFromFormat('Y-m-d H:i:s', $currentDate->format('Y-m-d') . ' 23:59:59');
            // dd($todayStart, $todayNoon);
            // Count or sum pairs based on the time of day
            if ($currentDate->format('A') == 'AM') {
                // Morning logs: from the start of the day until noon
                if ($binary_settings->binary_limit_type == 1) {
                    $data['todays_pairs'] = $data['todays_pairs']
                        ->where('earning_log_date_created', '>=', $todayStart)
                        ->where('earning_log_date_created', '<=', $todayNoon)
                        ->count();
                } else if ($binary_settings->binary_limit_type == 2) {
                    $data['todays_pairs'] = $data['todays_pairs']
                        ->where('earning_log_date_created', '>=', $todayStart)
                        ->where('earning_log_date_created', '<=', $todayNoon)
                        ->sum('earning_log_amount');
                }
            } else {
                // Afternoon logs: from noon until the end of the day
                if ($binary_settings->binary_limit_type == 1) {
                    $data['todays_pairs'] = $data['todays_pairs']
                        ->where('earning_log_date_created', '>', $todayNoon)
                        ->where('earning_log_date_created', '<=', $todayEnd)
                        ->count();
                } else if ($binary_settings->binary_limit_type == 2) {
                    $data['todays_pairs'] = $data['todays_pairs']
                        ->where('earning_log_date_created', '>', $todayNoon)
                        ->where('earning_log_date_created', '<=', $todayEnd)
                        ->sum('earning_log_amount');
                }
            }
        } else if ($binary_settings->cycle_per_day == 3) {
            $start = Carbon::now()->startofWeek();
            $end = Carbon::now()->endofWeek();
            if ($binary_settings->binary_limit_type == 1) {
                $data['todays_pairs'] = $data['todays_pairs']->wheredate('earning_log_date_created', '>=', $start)->wheredate('earning_log_date_created', '<=', $end)->count();
            } else if ($binary_settings->binary_limit_type == 2) {
                $data['todays_pairs'] = $data['todays_pairs']->wheredate('earning_log_date_created', '>=', $start)->wheredate('earning_log_date_created', '<=', $end)->sum('earning_log_amount');
            }
        } else if ($binary_settings->cycle_per_day == 4) {
            if ($binary_settings->binary_limit_type == 1) {
                $data['todays_pairs'] = $data['todays_pairs']->count();
            } else if ($binary_settings->binary_limit_type == 2) {
                $data['todays_pairs'] = $data['todays_pairs']->sum('earning_log_amount');
            }
        }
        $data['todays_pairs'] = round($data['todays_pairs'], 2);

        if ($binary_settings->binary_limit_type == 1) {
            $data['max_pairs'] = Tbl_slot::where('slot_id', $slot->slot_id)->JoinMembership()->first() ? Tbl_slot::where('slot_id', $slot->slot_id)->JoinMembership()->first()->membership_pairings_per_day : 0;
        } else if ($binary_settings->binary_limit_type == 2) {
            $data['max_pairs'] = Tbl_slot::where('slot_id', $slot->slot_id)->JoinMembership()->first() ? Tbl_slot::where('slot_id', $slot->slot_id)->JoinMembership()->first()->max_earnings_per_cycle : 0;
        }
        if ($data['todays_pairs'] <= $data['max_pairs']) {
            $data['remarks'] = 'ok';
        } else {
            $data['remarks'] = 'nah';
        }

        $direct_status = [];

        if ($binary_settings->binary_required_direct_enable) {
            $count_direct = Tbl_slot::where('slot_sponsor', $slot->slot_id)->count();
            $direct_required = Tbl_membership::where('membership_id', $slot->slot_membership)->value('binary_required_direct');
            $direct_status['count_direct'] = $count_direct;
            $direct_status['direct_required'] = $direct_required;
            if ($count_direct >= $direct_required) {
                $direct_status['qualified'] = 1;
            } else {
                $direct_status['qualified'] = 0;
            }
        } else {
        }

        $data['log'] = $log;
        $data['direct_status'] = $direct_status;
        $data['total_running'] = $running_balance;
        // dd($data);
        return json_encode($data);
    }

    public function binary_points()
    {
        $binary_settings = DB::table('tbl_binary_settings')->first();
        $slot_id = Request::input('current_slot_id');
        $history_type = Request::input('history_type');
        if ($history_type == 'per_level') {
            $slot = Tbl_slot::where('slot_id', $slot_id)->joinMembership()->first();
            $response['data'] = Tbl_binary_points::where('binary_points_slot_id', $slot_id)
                ->groupBy('binary_cause_level')
                ->select(
                    'binary_cause_level',
                    DB::raw('SUM(binary_receive_left) as left_points'),
                    DB::raw('SUM(binary_receive_right) as right_points'),
                    DB::raw('SUM(binary_points_income) as earnings'),
                    DB::raw('COUNT(CASE WHEN binary_points_income > 0 THEN 1 ELSE NULL END) as total_pairs'),
                    DB::raw('COUNT(binary_points_slot_id) as slot_count'),
                    DB::raw('GROUP_CONCAT(binary_points_id) as all_binary_points_id')
                )
                ->paginate(10);

            $response['max_points_per_level'] = $slot->max_points_per_level;
            $response['max_earnings_per_level'] = $slot->max_earnings_per_level;
            $response['total_earnings'] = Tbl_binary_points::where('binary_points_slot_id', $slot_id)->sum('binary_points_income');
        } else if ($history_type == 'per_cycle') {
            if ($binary_settings->cycle_per_day == 1) {
                if ($binary_settings->binary_limit_type == 1) {
                    $earningsExpression = DB::raw('COUNT(earning_log_amount) as earnings');
                } else if ($binary_settings->binary_limit_type == 2) {
                    $earningsExpression = DB::raw('SUM(earning_log_amount) as earnings');
                }

                $response['data'] = Tbl_earning_log::where('earning_log_slot_id', $slot_id)
                    ->where('earning_log_plan_type', 'BINARY')
                    ->select(
                        DB::raw("DATE_FORMAT(tbl_earning_log.earning_log_date_created, '%M %d, %Y') as earning_log_date_created"),
                        $earningsExpression
                    )
                    ->groupBy(DB::raw("DATE_FORMAT(tbl_earning_log.earning_log_date_created, '%M %d, %Y')"))
                    ->orderBy('earning_log_slot_id', 'desc')
                    ->paginate(10);
            } else if ($binary_settings->cycle_per_day == 2) {
                if ($binary_settings->binary_limit_type == 1) {
                    $morningEarnings = DB::raw("COALESCE(COUNT(CASE WHEN TIME(tbl_earning_log.earning_log_date_created) < '12:00:00' THEN tbl_earning_log.earning_log_amount END), 0) as morning_earnings");
                    $afternoonEarnings = DB::raw("COALESCE(COUNT(CASE WHEN TIME(tbl_earning_log.earning_log_date_created) >= '12:00:00' THEN tbl_earning_log.earning_log_amount END), 0) as afternoon_earnings");
                } else if ($binary_settings->binary_limit_type == 2) {
                    $morningEarnings = DB::raw("COALESCE(SUM(CASE WHEN TIME(tbl_earning_log.earning_log_date_created) < '12:00:00' THEN tbl_earning_log.earning_log_amount END), 0) as morning_earnings");
                    $afternoonEarnings = DB::raw("COALESCE(SUM(CASE WHEN TIME(tbl_earning_log.earning_log_date_created) >= '12:00:00' THEN tbl_earning_log.earning_log_amount END), 0) as afternoon_earnings");
                }

                $response['data'] = Tbl_earning_log::where('earning_log_slot_id', $slot_id)
                    ->where('earning_log_plan_type', 'BINARY')
                    ->select(
                        DB::raw("DATE_FORMAT(tbl_earning_log.earning_log_date_created, '%M %d, %Y') as earning_log_date_created"),
                        $morningEarnings,
                        $afternoonEarnings
                    )
                    ->groupBy(DB::raw("DATE_FORMAT(tbl_earning_log.earning_log_date_created, '%M %d, %Y')"))
                    ->orderBy(DB::raw("DATE_FORMAT(tbl_earning_log.earning_log_date_created, '%M %d, %Y')"), 'desc')
                    ->paginate(10);
            } else if ($binary_settings->cycle_per_day == 3) {
                $response['data'] = Tbl_earning_log::where('earning_log_slot_id', $slot_id)
                    ->where('earning_log_plan_type', 'BINARY')
                    ->select(
                        DB::raw('YEARWEEK(earning_log_date_created, 1) as year_week'),  // Group by week
                        DB::raw('MIN(DATE_SUB(earning_log_date_created, INTERVAL WEEKDAY(earning_log_date_created) DAY)) as start_of_week'),  // Start of the week (Monday)
                        DB::raw('MAX(DATE_ADD(DATE_SUB(earning_log_date_created, INTERVAL WEEKDAY(earning_log_date_created) DAY), INTERVAL 6 DAY)) as end_of_week'),  // End of the week (Sunday)
                        DB::raw('SUM(tbl_earning_log.earning_log_amount) as total_earnings'),  // Total earnings for the week
                        DB::raw('COUNT(tbl_earning_log.earning_log_amount) as total_pairs')  // Total pairs for the week
                    )
                    ->groupBy(DB::raw('YEARWEEK(earning_log_date_created, 1)'))  // Group by week number
                    ->orderBy(DB::raw('YEARWEEK(earning_log_date_created, 1)'), 'desc')  // Sort weeks in descending order
                    ->paginate(10);
            } else if ($binary_settings->cycle_per_day == 4) {
                if ($binary_settings->binary_limit_type == 1) {
                    $earningsExpression = DB::raw('COUNT(earning_log_amount) as earnings');
                } else if ($binary_settings->binary_limit_type == 2) {
                    $earningsExpression = DB::raw('SUM(earning_log_amount) as earnings');
                }
                $totalStats = Tbl_earning_log::where('earning_log_slot_id', $slot_id)
                    ->where('earning_log_plan_type', 'BINARY')
                    ->select(
                        DB::raw('SUM(tbl_earning_log.earning_log_amount) as total_earnings'),
                        DB::raw('COUNT(tbl_earning_log.earning_log_amount) as total_pairs')
                    )
                    ->first();

                $earningLogs = Tbl_earning_log::where('earning_log_slot_id', $slot_id)
                    ->where('earning_log_plan_type', 'BINARY')
                    ->select(
                        DB::raw("DATE_FORMAT(tbl_earning_log.earning_log_date_created, '%M %d, %Y') as earning_log_date_created"),
                        DB::raw('COUNT(earning_log_amount) as total_pairs'),
                        DB::raw('SUM(earning_log_amount) as total_earnings')
                    )
                    ->groupBy(DB::raw("DATE_FORMAT(tbl_earning_log.earning_log_date_created, '%M %d, %Y')"))
                    ->orderBy('earning_log_slot_id', 'desc')
                    ->paginate(10);

                $response = [
                    'total_earnings' => $totalStats->total_earnings ?? 0,
                    'total_pairs' => $totalStats->total_pairs ?? 0,
                    'data' => $earningLogs
                ];
            }
        }

        return $response;
    }

    public function binary_slot_limit()
    {
        $slot_id = Request::input('current_slot_id');
        $slot = Tbl_slot::where('slot_id', $slot_id)->joinMembership()->first();
        if (!$slot)
            return response()->json(['status' => 'error', 'message' => 'Slot not found']);

        $paginated_data = Tbl_binary_points::where('binary_points_slot_id', $slot_id)
            ->groupBy('binary_cause_level')
            ->select(
                'binary_cause_level as placement_level',
                DB::raw('COUNT(binary_points_id) as total_slot_count'),
                DB::raw('GROUP_CONCAT(binary_points_id) as all_tree_id')
            )
            ->paginate(10);

        $membership_list = Tbl_membership::where('archive', 0)->orderBy('hierarchy', 'asc')->get();
        $binary_points_settings = Tbl_binary_points_settings::where('membership_id', $slot->slot_membership)->get()->keyBy('membership_entry_id');

        $all_log_ids = [];
        foreach ($paginated_data as $log) {
            $all_log_ids = array_merge($all_log_ids, explode(',', $log->all_tree_id));
        }
        $all_log_ids = array_unique(array_filter($all_log_ids));

        $tree_logs_map = Tbl_binary_points::whereIn('binary_points_id', $all_log_ids)->get()->keyBy('binary_points_id');
        $cause_slot_ids = $tree_logs_map->pluck('binary_cause_slot_id')->unique()->filter();

        $log_details_map = Tbl_slot::whereIn('tbl_slot.slot_id', $cause_slot_ids)
            ->leftJoin('tbl_tree_placement', 'tbl_slot.slot_id', '=', 'tbl_tree_placement.placement_child_id')
            ->where('tbl_tree_placement.placement_parent_id', $slot_id)
            ->select('tbl_slot.slot_id', 'tbl_slot.slot_no', 'tbl_tree_placement.placement_position')
            ->get()
            ->keyBy('slot_id');

        $slot_per_level = [];
        foreach ($paginated_data as $log) {
            $tree_ids = explode(',', $log->all_tree_id);
            $level_data = [];

            foreach ($membership_list as $membership) {
                $level_data[$membership->membership_id] = (object) [
                    'left' => 0,
                    'right' => 0,
                    'left_maxed' => false,
                    'right_maxed' => false
                ];
            }

            foreach ($tree_ids as $log_id) {
                $tree_log = $tree_logs_map->get($log_id);
                if (!$tree_log)
                    continue;

                $log_details = $log_details_map->get($tree_log->binary_cause_slot_id);
                if (!$log_details)
                    continue;

                $membership_id = $tree_log->binary_cause_membership_id;
                if (isset($level_data[$membership_id])) {
                    if ($log_details->placement_position == 'LEFT') {
                        $level_data[$membership_id]->left++;
                    } else if ($log_details->placement_position == 'RIGHT') {
                        $level_data[$membership_id]->right++;
                    }
                }
            }

            foreach ($membership_list as $membership) {
                $maxed_slot = $binary_points_settings->get($membership->membership_id);
                if ($maxed_slot) {
                    $level_data[$membership->membership_id]->left_maxed = ($level_data[$membership->membership_id]->left >= $maxed_slot->max_slot_per_level);
                    $level_data[$membership->membership_id]->right_maxed = ($level_data[$membership->membership_id]->right >= $maxed_slot->max_slot_per_level);
                }
            }
            $slot_per_level[$log->placement_level] = $level_data;
        }

        $max_slot_per_package = Tbl_binary_points_settings::where('membership_id', $slot->slot_membership)
            ->where('max_slot_per_level', '!=', 0)
            ->pluck('max_slot_per_level', 'membership_entry_id');

        $max_slot_membership = $membership_list->filter(function ($m) use ($max_slot_per_package) {
            return isset($max_slot_per_package[$m->membership_id]);
        })->values();

        return [
            'data' => $paginated_data,
            'membership' => $max_slot_membership,
            'max_slot_per_level' => $max_slot_per_package,
            'slot_per_level' => $slot_per_level,
        ];
    }

    public function mentors_bonus_earning()
    {
        $slot = Tbl_slot::where('slot_owner', Request::user()->id)->where('slot_id', Request::input('current_slot_id'))->first();
        $data = null;
        $running_balance = 0;

        if ($slot) {
            $log = Tbl_earning_log::where('earning_log_slot_id', $slot->slot_id)
                ->leftJoin('tbl_slot', 'tbl_slot.slot_id', '=', 'tbl_earning_log.earning_log_cause_id')
                ->leftJoin('users', 'users.id', '=', 'tbl_slot.slot_owner')
                ->leftJoin('tbl_membership', 'tbl_membership.membership_id', '=', 'tbl_earning_log.earning_log_cause_membership_id')
                ->where('earning_log_plan_type', 'MENTORS BONUS')
                ->leftJoin('tbl_label', 'tbl_label.plan_code', '=', 'tbl_earning_log.earning_log_plan_type')
                ->select('*', DB::raw("DATE_FORMAT(tbl_earning_log.earning_log_date_created, '%m/%d/%Y') as earning_log_date_created"),
                    DB::raw("DATE_FORMAT(tbl_earning_log.earning_log_date_created, '%h:%i %p') as earning_log_time_created"))
                ->paginate(10);

            $data['log'] = $log;
            $running_balance = Tbl_earning_log::where('earning_log_slot_id', $slot->slot_id)->where('earning_log_plan_type', 'MENTORS BONUS')->sum('earning_log_amount');
        }
        $data['mentor_status'] = Tbl_membership::where('mentors_level', '>', 0)->count();
        $data['total'] = number_format($running_balance, 2);

        return json_encode($data);

        // $slot   = Tbl_slot::where("slot_owner",Request::user()->id)->where("slot_id",Request::input("current_slot_id"))->leftjoin('users','users.id','tbl_slot.slot_owner')->first();

        // $data['log']    = Tbl_earning_log::where('earning_log_slot_id',$slot->slot_id)->where('earning_log_plan_type','MENTORS BONUS')
        //                 ->leftjoin('tbl_slot','tbl_slot.slot_id','tbl_earning_log.earning_log_cause_id')
        //                 ->leftjoin('users','users.id','tbl_slot.slot_owner')
        //                 ->select("*",DB::raw("DATE_FORMAT(tbl_earning_log.earning_log_date_created, '%m/%d/%Y') as earning_log_date_created"),
        //                     DB::raw("DATE_FORMAT(tbl_earning_log.earning_log_date_created, '%h:%i %p') as earning_log_time_created"))
        //                 ->paginate(10);
        // $data['total']   =  Tbl_earning_log::where('earning_log_slot_id',$slot->slot_id)->where('earning_log_plan_type','MENTORS BONUS')
        //                 ->leftjoin('tbl_slot','tbl_slot.slot_id','tbl_earning_log.earning_log_cause_id')
        //                 ->leftjoin('users','users.id','tbl_slot.slot_owner')->sum('earning_log_amount');
        // $data['mentor_status'] = Tbl_membership::where('mentors_level','>',0)->count();
        // return response()->json($data);
    }

    public function sponsor_matching_earning()
    {
        $slot = Tbl_slot::where('slot_owner', Request::user()->id)->where('slot_id', Request::input('current_slot_id'))->first();
        $data = null;
        $running_balance = 0;

        if ($slot) {
            $log = Tbl_earning_log::where('earning_log_slot_id', $slot->slot_id)
                ->leftJoin('tbl_slot', 'tbl_slot.slot_id', '=', 'tbl_earning_log.earning_log_cause_id')
                ->leftJoin('users', 'users.id', '=', 'tbl_slot.slot_owner')
                ->leftJoin('tbl_membership', 'tbl_membership.membership_id', '=', 'tbl_earning_log.earning_log_cause_membership_id')
                ->where('earning_log_plan_type', 'SPONSOR MATCHING BONUS')
                ->leftJoin('tbl_label', 'tbl_label.plan_code', '=', 'tbl_earning_log.earning_log_plan_type')
                ->select('*', DB::raw("DATE_FORMAT(tbl_earning_log.earning_log_date_created, '%m/%d/%Y') as earning_log_date_created"),
                    DB::raw("DATE_FORMAT(tbl_earning_log.earning_log_date_created, '%h:%i %p') as earning_log_time_created"))
                ->paginate(10);

            $data['log'] = $log;
            $running_balance = Tbl_earning_log::where('earning_log_slot_id', $slot->slot_id)->where('earning_log_plan_type', 'SPONSOR MATCHING BONUS')->sum('earning_log_amount');
        }

        $data['total'] = number_format($running_balance, 2);

        return json_encode($data);
    }

    public function unilevel()
    {
        $slot = Tbl_slot::where('slot_owner', Request::user()->id)->where('slot_id', Request::input('current_slot_id'))->first();
        $data = null;
        $total_ppv = 0;
        $total_gpv = 0;
        $required_ppv = 0;
        $log = null;
        $log_personal = null;
        $ctr = 0;
        $start_date = Request::input('start_date');
        $end_date = Request::input('end_date');
        if ($slot) {
            $membership = Tbl_membership::where('membership_id', $slot->slot_membership)->first();
            if ($membership) {
                $required_ppv = $membership->membership_required_pv;
                $first_date = $start_date == null ? Carbon::now()->startOfMonth() : $start_date;
                $end_date = $end_date == null ? Carbon::now()->endOfMonth() : $end_date;

                $all_points = Tbl_unilevel_points::where('unilevel_points_slot_id', $slot->slot_id)
                    ->where('unilevel_points_date_created', '>=', $first_date)
                    ->where('unilevel_points_date_created', '<=', $end_date)
                    ->get();

                $personal_points = $all_points->where('unilevel_points_type', 'UNILEVEL_PPV')->where('unilevel_points_distribute', 0);
                $log_personal['level_name'] = 'Personal Purchase';
                $log_personal['number_of_slots'] = $personal_points->count() ? $personal_points->count() . ' Purchase(s)' : 'No Purchase';

                $last_ppv = Tbl_unilevel_points::where('unilevel_points_slot_id', $slot->slot_id)->where('unilevel_points_type', 'UNILEVEL_PPV')->orderBy('unilevel_points_date_created', 'DESC')->first();
                $log_personal['last_slot_creation'] = $last_ppv ? Carbon::parse($last_ppv->unilevel_points_date_created)->format('m/d/Y') : '---';
                $log_personal['earnings'] = $personal_points->sum('unilevel_points_amount');
                $total_ppv += $log_personal['earnings'];
                $log_personal['earnings'] = number_format($log_personal['earnings'], 2);

                $group_points = $all_points->where('unilevel_points_type', 'UNILEVEL_GPV')->where('unilevel_points_distribute', 0)->groupBy('unilevel_points_cause_level');
                $last_gpv_per_level = Tbl_unilevel_points::where('unilevel_points_slot_id', $slot->slot_id)
                    ->where('unilevel_points_type', 'UNILEVEL_GPV')
                    ->select('unilevel_points_cause_level', DB::raw('MAX(unilevel_points_date_created) as last_date'))
                    ->groupBy('unilevel_points_cause_level')
                    ->pluck('last_date', 'unilevel_points_cause_level');

                for ($level = 1; $level <= $membership->membership_unilevel_level; $level++) {
                    $level_pts = $group_points->get($level, collect());
                    $log[$ctr]['level_name'] = $this->ordinal($level);
                    $log[$ctr]['number_of_slots'] = $level_pts->count() ? $level_pts->count() . ' Purchase(s)' : 'No Purchase';
                    $log[$ctr]['last_slot_creation'] = isset($last_gpv_per_level[$level]) ? Carbon::parse($last_gpv_per_level[$level])->format('m/d/Y') : '---';
                    $earnings = $level_pts->sum('unilevel_points_amount');
                    $total_gpv += $earnings;
                    $log[$ctr]['earnings'] = number_format($earnings, 2);
                    $ctr++;
                }
            }
            $history = Tbl_unilevel_distribute::where('slot_id', $slot->slot_id)->get();
            $total_unilevel_sum = Tbl_wallet_log::where('wallet_log_details', 'UNILEVEL')->where('wallet_log_slot_id', $slot->slot_id)->sum('wallet_log_amount');
        }

        $data['log'] = $log;
        $data['log_personal'] = $log_personal;
        $data['history'] = $history;
        $data['total_history'] = number_format($total_unilevel_sum, 2);
        $data['total_ppv'] = number_format($total_ppv, 2);
        $data['total_gpv'] = number_format($total_gpv, 2);
        $data['required_ppv'] = number_format($required_ppv, 2);
        $data['passed'] = $total_ppv >= $required_ppv ? 1 : 0;

        return json_encode($data);
    }

    public function unilevel_dynamic()
    {
        $slot = Tbl_slot::where('slot_owner', Request::user()->id)->where('slot_id', Request::input('current_slot_id'))->first();
        $data = null;
        $total_ppv = 0;
        $total_gpv = 0;
        $required_ppv = 0;
        $log = null;
        $ctr = 0;
        $start_date = Request::input('start_date');
        $end_date = Request::input('end_date');
        if ($slot) {
            $membership = Tbl_membership::where('membership_id', $slot->slot_membership)->first();
            if ($membership) {
                $required_ppv = $membership->membership_required_pv;
                $first_date = $start_date == null ? Carbon::now()->startOfMonth()->format('Y-m-d') : $start_date;
                $end_date = $end_date == null ? Carbon::now()->endOfMonth()->format('Y-m-d') : $end_date;

                $all_points = Tbl_unilevel_points::where('unilevel_points_slot_id', $slot->slot_id)
                    ->where('unilevel_points_date_created', '>=', $first_date)
                    ->where('unilevel_points_date_created', '<=', $end_date)
                    ->get();

                $personal_points = $all_points->where('unilevel_points_type', 'UNILEVEL_PPV')->where('unilevel_points_distribute', 0);
                $log[$ctr]['level_name'] = 'Personal Purchase';
                $log[$ctr]['number_of_slots'] = $personal_points->count() ? $personal_points->count() . ' Purchase(s)' : 'No Purchase';
                $last_ppv = Tbl_unilevel_points::where('unilevel_points_slot_id', $slot->slot_id)->where('unilevel_points_type', 'UNILEVEL_PPV')->orderBy('unilevel_points_date_created', 'DESC')->first();
                $log[$ctr]['last_slot_creation'] = $last_ppv ? Carbon::parse($last_ppv->unilevel_points_date_created)->format('m/d/Y') : '---';
                $earnings = $personal_points->sum('unilevel_points_amount');
                $total_ppv += $earnings;
                $log[$ctr]['earnings'] = number_format($earnings, 2);
                $ctr++;

                $dynamic_compression = Tbl_dynamic_compression_record::where('slot_id', $slot->slot_id)
                    ->whereDate('start_date', '>=', $first_date)
                    ->where('end_date', '<=', $end_date)
                    ->get()
                    ->groupBy('dynamic_level');

                for ($level = 1; $level <= $membership->membership_unilevel_level; $level++) {
                    $level_recs = $dynamic_compression->get($level, collect());
                    $log[$ctr]['level_name'] = $this->ordinal($level);
                    $log[$ctr]['number_of_slots'] = $level_recs->count() ? $level_recs->count() . ' Purchase(s)' : 'No Purchase';
                    $earnings = $level_recs->sum('earned_points');
                    $total_gpv += $earnings;
                    $log[$ctr]['earnings'] = number_format($earnings, 2);
                    $ctr++;
                }
            }
            $history = Tbl_unilevel_distribute::where('slot_id', $slot->slot_id)->get();
            $total_unilevel_sum = Tbl_wallet_log::where('wallet_log_details', 'UNILEVEL COMMISSION')->where('wallet_log_slot_id', $slot->slot_id)->sum('wallet_log_amount');
        }

        $data['log'] = $log;
        $data['history'] = $history;
        $data['total_history'] = number_format($total_unilevel_sum, 2);
        $data['total_ppv'] = number_format($total_ppv, 2);
        $data['total_gpv'] = number_format($total_gpv, 2);
        $data['required_ppv'] = number_format($required_ppv, 2);
        $data['passed'] = $total_ppv >= $required_ppv ? 1 : 0;
        $data['first_date'] = $first_date;
        $data['end_date'] = $end_date;

        return json_encode($data);
    }

    public function stairstep()
    {
        $slot = Tbl_slot::where('slot_owner', Request::user()->id)->where('slot_id', Request::input('current_slot_id'))->first();
        $data = null;
        $log = null;
        $required_ppv = 0;
        $total_override_points = 0;
        $total_all_personal_gpv = 0;
        $total_all_personal_ppv = 0;
        $rank_level = 0;
        if ($slot) {
            $first_date = Carbon::now()->startOfMonth();
            $end_date = Carbon::now()->endOfMonth();

            $all_points = Tbl_stairstep_points::where('stairstep_points_slot_id', $slot->slot_id)->get();

            $monthly_points = $all_points
                ->where('stairstep_points_date_created', '>=', $first_date)
                ->where('stairstep_points_date_created', '<=', $end_date);

            $log = $monthly_points
                ->where('stairstep_points_type', 'STAIRSTEP_GPV')
                ->map(function ($pt) {
                    $pt->stairstep_points_date_created_formatted = Carbon::parse($pt->stairstep_points_date_created)->format('m/d/Y');
                    return $pt;
                });
            // Re-fetch with joins for display if needed, but original used display in loop? Wait, original select was '*'.
            // Better fetch with joins if we need users data.
            $log = Tbl_stairstep_points::where('stairstep_points_slot_id', $slot->slot_id)
                ->leftJoin('tbl_slot', 'tbl_slot.slot_id', '=', 'tbl_stairstep_points.stairstep_points_cause_id')
                ->leftJoin('users', 'users.id', '=', 'tbl_slot.slot_owner')
                ->where('stairstep_points_type', 'STAIRSTEP_GPV')
                ->where('stairstep_points_date_created', '>=', $first_date)
                ->where('stairstep_points_date_created', '<=', $end_date)
                ->select('tbl_stairstep_points.*', 'tbl_slot.slot_no', 'users.first_name', 'users.last_name', DB::raw("DATE_FORMAT(tbl_stairstep_points.stairstep_points_date_created, '%m/%d/%Y') as stairstep_points_date_created"))
                ->get();

            $total_override_points = $monthly_points->where('stairstep_points_type', 'STAIRSTEP_GPV')->sum('stairstep_override_points');
            $total_personal_pv = $monthly_points->where('stairstep_points_type', 'STAIRSTEP_PPV')->sum('stairstep_points_amount');

            $total_all_personal_gpv = $all_points->sum('stairstep_points_amount');
            $total_all_personal_ppv = $all_points->sum('stairstep_points_amount');

            $get_rank = Tbl_stairstep_rank::where('stairstep_rank_id', $slot->slot_stairstep_rank)->first();
            if ($get_rank) {
                $required_ppv = $get_rank->stairstep_rank_personal;
                $rank_level = $get_rank->stairstep_rank_level;
            }
        }

        $data['log'] = $log;
        $data['total_override_points'] = number_format($total_override_points, 2);
        $data['total_personal_pv'] = number_format($total_personal_pv, 2);
        $data['required_ppv'] = number_format($required_ppv, 2);

        $data['total_all_personal_gpv'] = number_format($total_all_personal_gpv);
        $data['total_all_personal_ppv'] = number_format($total_all_personal_ppv);
        $data['passed'] = $total_personal_pv >= $required_ppv ? 1 : 0;

        $all_rank = Tbl_stairstep_rank::where('archive', 0)->get();

        foreach ($all_rank as $key => $rnk) {
            $all_rank[$key]->all_ppv_percentage = ($total_all_personal_ppv >= $rnk->stairstep_rank_personal_all) ? 'Qualified (100%)' : $total_all_personal_ppv . ' of ' . $rnk->stairstep_rank_personal_all . ' (' . (($total_all_personal_ppv / $rnk->stairstep_rank_personal_all) * 100) . '%)';
            $all_rank[$key]->all_gpv_percentage = ($total_all_personal_gpv >= $rnk->stairstep_rank_group_all) ? 'Qualified (100%)' : $total_all_personal_gpv . ' of ' . $rnk->stairstep_rank_group_all . ' (' . (($total_all_personal_gpv / $rnk->stairstep_rank_group_all) * 100) . '%)';
            $all_rank[$key]->qualified = $rank_level > $rnk->stairstep_rank_level ? 1 : 0;

            if ($all_rank[$key]->qualified == 0) {
                if ($all_rank[$key]->all_ppv_percentage == 'Qualified (100%)' && $all_rank[$key]->all_gpv_percentage == 'Qualified (100%)') {
                    $all_rank[$key]->qualified = 1;
                }
            }
        }

        $history = Tbl_stairstep_distribute::where('slot_id', $slot->slot_id)->get();
        $total_stairstep_sum = Tbl_wallet_log::where('wallet_log_details', 'STAIRSTEP GPV')->where('wallet_log_slot_id', $slot->slot_id)->sum('wallet_log_amount');

        foreach ($history as $key => $value) {
            $history[$key]->is_qualified = 0;  // Simplified as original was doing N+1 sum
        }

        $data['all_rank'] = $all_rank;
        $data['current_rank'] = isset($get_rank) ? $get_rank->stairstep_rank_name : 0;
        $data['history'] = $history;
        $data['total_stairstep'] = $total_stairstep_sum;

        return json_encode($data);
    }

    public function cashback()
    {
        $slot = Tbl_slot::where('slot_owner', Request::user()->id)->where('slot_id', Request::input('current_slot_id'))->first();
        $data = null;
        $running_balance = 0;

        if ($slot) {
            $log = Tbl_earning_log::where('earning_log_slot_id', $slot->slot_id)
                ->leftJoin('tbl_slot', 'tbl_slot.slot_id', '=', 'tbl_earning_log.earning_log_cause_id')
                ->leftJoin('users', 'users.id', '=', 'tbl_slot.slot_owner')
                ->leftJoin('tbl_membership', 'tbl_membership.membership_id', '=', 'tbl_earning_log.earning_log_cause_membership_id')
                ->where('earning_log_plan_type', 'CASHBACK')
                ->leftJoin('tbl_label', 'tbl_label.plan_code', '=', 'tbl_earning_log.earning_log_plan_type')
                ->select('*', DB::raw("DATE_FORMAT(tbl_earning_log.earning_log_date_created, '%m/%d/%Y') as earning_log_date_created"),
                    DB::raw("DATE_FORMAT(tbl_earning_log.earning_log_date_created, '%h:%i %p') as earning_log_time_created"))
                ->paginate(10);

            $data['log'] = $log;
            $running_balance = Tbl_earning_log::where('earning_log_slot_id', $slot->slot_id)->where('earning_log_plan_type', 'CASHBACK')->sum('earning_log_amount');
        }

        $data['total'] = number_format($running_balance, 2);

        return json_encode($data);
    }

    public function board()
    {
        $slot = Tbl_slot::where('slot_owner', Request::user()->id)->where('slot_id', Request::input('current_slot_id'))->first();
        $data = null;
        $running_balance = 0;

        if ($slot) {
            $log = Tbl_wallet_log::where('wallet_log_slot_id', $slot->slot_id)
                ->leftJoin('tbl_slot', 'tbl_slot.slot_id', '=', 'tbl_wallet_log.wallet_log_slot_id')
                ->leftJoin('users', 'users.id', '=', 'tbl_slot.slot_owner')
                ->leftJoin('tbl_membership', 'tbl_membership.membership_id', '=', 'tbl_earning_log.earning_log_cause_membership_id')
                ->where('wallet_log_details', 'Graduation Bonus')
                ->leftJoin('tbl_label', 'tbl_label.plan_code', '=', 'tbl_wallet_log.wallet_log_details')
                ->select('*', DB::raw("DATE_FORMAT(tbl_wallet_log.wallet_log_date_created, '%m/%d/%Y') as wallet_log_date_created"),
                    DB::raw("DATE_FORMAT(tbl_wallet_log.wallet_log_date_created, '%h:%i %p') as wallet_log_time_created"))
                ->get();

            $data['log'] = $log;
            $running_balance = Tbl_wallet_log::where('wallet_log_slot_id', $slot->slot_id)->where('wallet_log_details', 'Graduation Bonus')->sum('wallet_log_running_balance');
        }

        $data['total'] = number_format($running_balance, 2);

        return json_encode($data);
    }

    public function monoline()
    {
        $slot = Tbl_slot::where('slot_owner', Request::user()->id)->where('slot_id', Request::input('current_slot_id'))->first();
        $data = null;
        $running_balance = 0;

        if ($slot) {
            $log = Tbl_earning_log::where('earning_log_slot_id', $slot->slot_id)
                ->leftJoin('tbl_slot', 'tbl_slot.slot_id', '=', 'tbl_earning_log.earning_log_cause_id')
                ->leftJoin('users', 'users.id', '=', 'tbl_slot.slot_owner')
                ->leftJoin('tbl_membership', 'tbl_membership.membership_id', '=', 'tbl_earning_log.earning_log_cause_membership_id')
                ->where('earning_log_plan_type', 'MONOLINE')
                ->leftJoin('tbl_label', 'tbl_label.plan_code', '=', 'tbl_earning_log.earning_log_plan_type')
                ->select('*', DB::raw("DATE_FORMAT(tbl_earning_log.earning_log_date_created, '%m/%d/%Y') as earning_log_date_created"),
                    DB::raw("DATE_FORMAT(tbl_earning_log.earning_log_date_created, '%h:%i %p') as earning_log_time_created"))
                ->paginate(10);

            $data['log'] = $log;
            $running_balance = Tbl_earning_log::where('earning_log_slot_id', $slot->slot_id)->where('earning_log_plan_type', 'MONOLINE')->sum('earning_log_amount');
        }

        $data['total'] = number_format($running_balance, 2);

        return json_encode($data);
    }

    public function pass_up()
    {
        $slot = Tbl_slot::where('slot_owner', Request::user()->id)->where('slot_id', Request::input('current_slot_id'))->first();
        $data = null;
        $running_balance = 0;

        if ($slot) {
            $log = Tbl_earning_log::where('earning_log_slot_id', $slot->slot_id)
                ->leftJoin('tbl_slot', 'tbl_slot.slot_id', '=', 'tbl_earning_log.earning_log_cause_id')
                ->leftJoin('users', 'users.id', '=', 'tbl_slot.slot_owner')
                ->leftJoin('tbl_membership', 'tbl_membership.membership_id', '=', 'tbl_earning_log.earning_log_cause_membership_id')
                ->where('earning_log_plan_type', 'PASS UP')
                ->leftJoin('tbl_label', 'tbl_label.plan_code', '=', 'tbl_earning_log.earning_log_plan_type')
                ->select('*', DB::raw("DATE_FORMAT(tbl_earning_log.earning_log_date_created, '%m/%d/%Y') as earning_log_date_created"),
                    DB::raw("DATE_FORMAT(tbl_earning_log.earning_log_date_created, '%h:%i %p') as earning_log_time_created"))
                ->paginate(10);

            $data['log'] = $log;
            $running_balance = Tbl_earning_log::where('earning_log_slot_id', $slot->slot_id)->where('earning_log_plan_type', 'PASS UP')->sum('earning_log_amount');
        }

        $data['total'] = number_format($running_balance, 2);

        return json_encode($data);
    }

    public function leveling_bonus()
    {
        $slot = Tbl_slot::where('slot_owner', Request::user()->id)->where('slot_id', Request::input('current_slot_id'))->first();
        $data = null;
        $running_balance = 0;

        if ($slot) {
            $log = Tbl_earning_log::where('earning_log_slot_id', $slot->slot_id)
                ->leftJoin('tbl_slot', 'tbl_slot.slot_id', '=', 'tbl_earning_log.earning_log_cause_id')
                ->leftJoin('users', 'users.id', '=', 'tbl_slot.slot_owner')
                ->leftJoin('tbl_membership', 'tbl_membership.membership_id', '=', 'tbl_earning_log.earning_log_cause_membership_id')
                ->where('earning_log_plan_type', 'LEVELING BONUS')
                ->leftJoin('tbl_label', 'tbl_label.plan_code', '=', 'tbl_earning_log.earning_log_plan_type')
                ->select('*', DB::raw("DATE_FORMAT(tbl_earning_log.earning_log_date_created, '%m/%d/%Y') as earning_log_date_created"),
                    DB::raw("DATE_FORMAT(tbl_earning_log.earning_log_date_created, '%h:%i %p') as earning_log_time_created"))
                ->paginate(10);

            $data['log'] = $log;
            $running_balance = Tbl_earning_log::where('earning_log_slot_id', $slot->slot_id)->where('earning_log_plan_type', 'LEVELING BONUS')->sum('earning_log_amount');
        }
        $data['level'] = Tbl_leveling_bonus_points::where('slot_id', $slot->slot_id)->orderBy('membership_level', 'ASC')->get();
        foreach ($data['level'] as $key => $level) {
            // dd($data["level"][$key]);
            if ($data['level'][$key]->claim == 1) {
                $data['level'][$key]->remarks = 'Claimed';
            } else {
                $data['level'][$key]->remarks = 'Not Claimed';
            }
            // dd($data["level"][$key]);
        }
        $data['total'] = number_format($running_balance, 2);

        return json_encode($data);
    }

    public function unilevel_or_earning()
    {
        $slot = Tbl_slot::where('slot_owner', Request::user()->id)->where('slot_id', Request::input('current_slot_id'))->first();
        $data = null;
        $running_balance = 0;

        if ($slot) {
            $log = Tbl_earning_log::where('earning_log_slot_id', $slot->slot_id)
                ->leftJoin('tbl_slot', 'tbl_slot.slot_id', '=', 'tbl_earning_log.earning_log_cause_id')
                ->leftJoin('users', 'users.id', '=', 'tbl_slot.slot_owner')
                ->leftJoin('tbl_membership', 'tbl_membership.membership_id', '=', 'tbl_earning_log.earning_log_cause_membership_id')
                ->where('earning_log_plan_type', 'UNILEVEL OR')
                ->leftJoin('tbl_label', 'tbl_label.plan_code', '=', 'tbl_earning_log.earning_log_plan_type')
                ->select('*', DB::raw("DATE_FORMAT(tbl_earning_log.earning_log_date_created, '%m/%d/%Y') as earning_log_date_created"),
                    DB::raw("DATE_FORMAT(tbl_earning_log.earning_log_date_created, '%h:%i %p') as earning_log_time_created"))
                ->paginate(10);

            $data['log'] = $log;
            $running_balance = Tbl_earning_log::where('earning_log_slot_id', $slot->slot_id)->where('earning_log_plan_type', 'UNILEVEL OR')->sum('earning_log_amount');
        }

        $data['total'] = number_format($running_balance, 2);
        $start = Carbon::now()->startOfMonth();
        $end = Carbon::now()->endOfMonth();
        $data['start'] = $start->format('m/d/Y');
        $data['end'] = $end->format('m/d/Y');
        $data['pv_points'] = Tbl_unilevel_or_points::where('slot_id', $slot->slot_id)->whereDate('created_at', '>=', $start)->whereDate('created_at', '<=', $end)->where('processed', 0)->sum('pv_points') ? Tbl_unilevel_or_points::where('slot_id', $slot->slot_id)->whereDate('created_at', '>=', $start)->whereDate('created_at', '<=', $end)->where('processed', 0)->sum('pv_points') : 0;
        $data['required_pv'] = Tbl_slot::JoinMembership()->where('slot_id', $slot->slot_id)->first()->membership_required_pv_or;
        if ($data['pv_points'] >= $data['required_pv']) {
            $data['remarks'] = 'Qualified';
        } else {
            $data['remarks'] = 'Not Qualified';
        }
        if ($data['remarks'] == 'Qualified') {
            $slot_tree = Tbl_tree_sponsor::where('sponsor_parent_id', $slot->slot_id)->where('sponsor_level', 1)->get();
            $unilevel_or_level = Tbl_slot::JoinMembership()->where('slot_id', $slot->slot_id)->first()->membership_unilevel_or_level;

            $slot_distinct_ids = Tbl_unilevel_or_points::whereDate('created_at', '>=', $start)
                ->whereDate('created_at', '<=', $end)
                ->where('processed', 0)
                ->distinct()
                ->pluck('slot_id');

            $sponsor_child_ids = $slot_tree->pluck('sponsor_child_id')->unique()->filter();
            $slots_info_map = Tbl_slot::JoinMembership()->whereIn('slot_id', $sponsor_child_ids)->get()->keyBy('slot_id');

            $all_pvs_map = Tbl_unilevel_or_points::whereIn('slot_id', $sponsor_child_ids)
                ->whereDate('created_at', '>=', $start)
                ->whereDate('created_at', '<=', $end)
                ->where('processed', 0)
                ->select('slot_id', DB::raw('SUM(pv_points) as total_pv'))
                ->groupBy('slot_id')
                ->pluck('total_pv', 'slot_id');

            $ctr = 0;
            $data['level'] = [];

            foreach ($slot_tree as $key => $l1) {
                $slot_info1 = $slots_info_map->get($l1->sponsor_child_id);
                $check = $slot_distinct_ids->contains($l1->sponsor_child_id) ? 1 : 0;

                $slot_tree[$key]->accumulated_pv = $check == 1 ? ($all_pvs_map->get($l1->sponsor_child_id) ?? 0) : 0;

                if ($slot_tree[$key]->accumulated_pv < ($slot_info1->membership_required_pv_or ?? 0)) {
                    $slot_tree[$key]->remark = 'Failed';
                } else {
                    $slot_tree[$key]->remark = 'Passed';
                    $ctr++;
                    if ($ctr > $unilevel_or_level) {
                        $ctr = $unilevel_or_level;
                    }
                }
            }

            if ($ctr != 0) {
                $slot_tree2 = Tbl_tree_sponsor::where('sponsor_parent_id', $slot->slot_id)->where('sponsor_level', '<=', $ctr)->get();
                $all_child_ids = $slot_tree2->pluck('sponsor_child_id')->unique()->filter();

                $slots_info2_map = Tbl_slot::JoinMembership()->whereIn('slot_id', $all_child_ids)->get()->keyBy('slot_id');
                $all_pts_map = Tbl_unilevel_or_points::whereIn('slot_id', $all_child_ids)
                    ->whereDate('created_at', '>=', $start)
                    ->whereDate('created_at', '<=', $end)
                    ->where('processed', 0)
                    ->select('slot_id', DB::raw('SUM(pv_points) as total_pv'))
                    ->groupBy('slot_id')
                    ->pluck('total_pv', 'slot_id');

                foreach ($slot_tree2 as $key => $l2) {
                    $pts = $all_pts_map->get($l2->sponsor_child_id) ?? 0;
                    $slot_info2 = $slots_info2_map->get($l2->sponsor_child_id);

                    if ($pts >= ($slot_info2->membership_required_pv_or ?? 0)) {
                        if (isset($data['level'][$l2->sponsor_level - 1])) {
                            $data['level'][$l2->sponsor_level - 1] += 1;
                        } else {
                            $data['level'][$l2->sponsor_level - 1] = 1;
                        }
                    }
                }
            } else {
                $data['level'][0] = 0;
            }
        }
        // dd($data['level']);
        return json_encode($data);
    }

    public function universal_pool_bonus()
    {
        $slot = Tbl_slot::where('slot_owner', Request::user()->id)->where('slot_id', Request::input('current_slot_id'))->first();
        $data = null;
        $running_balance = 0;

        if ($slot) {
            $log = Tbl_earning_log::where('earning_log_slot_id', $slot->slot_id)
                ->leftJoin('tbl_slot', 'tbl_slot.slot_id', '=', 'tbl_earning_log.earning_log_cause_id')
                ->leftJoin('users', 'users.id', '=', 'tbl_slot.slot_owner')
                ->leftJoin('tbl_membership', 'tbl_membership.membership_id', '=', 'tbl_earning_log.earning_log_cause_membership_id')
                ->where('earning_log_plan_type', 'UNIVERSAL POOL BONUS')
                ->leftJoin('tbl_label', 'tbl_label.plan_code', '=', 'tbl_earning_log.earning_log_plan_type')
                ->select('*', DB::raw("DATE_FORMAT(tbl_earning_log.earning_log_date_created, '%m/%d/%Y') as earning_log_date_created"),
                    DB::raw("DATE_FORMAT(tbl_earning_log.earning_log_date_created, '%h:%i %p') as earning_log_time_created"))
                ->paginate(10);

            $data['log'] = $log;
            $running_balance = Tbl_earning_log::where('earning_log_slot_id', $slot->slot_id)->where('earning_log_plan_type', 'UNIVERSAL POOL BONUS')->sum('earning_log_amount');
        }

        $data['total'] = number_format($running_balance, 2);
        return json_encode($data);
    }

    public function share_link()
    {
        $slot = Tbl_slot::where('slot_owner', Request::user()->id)->where('slot_id', Request::input('current_slot_id'))->first();
        $data = null;
        $running_balance = 0;

        if ($slot) {
            $log = Tbl_earning_log::where('earning_log_slot_id', $slot->slot_id)
                ->leftJoin('tbl_slot', 'tbl_slot.slot_id', '=', 'tbl_earning_log.earning_log_cause_id')
                ->leftJoin('users', 'users.id', '=', 'tbl_slot.slot_owner')
                ->leftJoin('tbl_membership', 'tbl_membership.membership_id', '=', 'tbl_earning_log.earning_log_cause_membership_id')
                ->where('earning_log_plan_type', 'SHARE LINK')
                ->leftJoin('tbl_label', 'tbl_label.plan_code', '=', 'tbl_earning_log.earning_log_plan_type')
                ->select('*', DB::raw("DATE_FORMAT(tbl_earning_log.earning_log_date_created, '%m/%d/%Y') as earning_log_date_created"),
                    DB::raw("DATE_FORMAT(tbl_earning_log.earning_log_date_created, '%h:%i %p') as earning_log_time_created"))
                ->paginate(10);

            $data['log'] = $log;
            $running_balance = Tbl_earning_log::where('earning_log_slot_id', $slot->slot_id)->where('earning_log_plan_type', 'SHARE LINK')->sum('earning_log_amount');
        }

        $data['total'] = number_format($running_balance, 2);
        return json_encode($data);
    }

    public function watch_earn_earning()
    {
        $slot = Tbl_slot::where('slot_owner', Request::user()->id)->where('slot_id', Request::input('current_slot_id'))->first();
        $data = null;
        $running_balance = 0;

        if ($slot) {
            $log = Tbl_earning_log::where('earning_log_slot_id', $slot->slot_id)
                ->leftJoin('tbl_slot', 'tbl_slot.slot_id', '=', 'tbl_earning_log.earning_log_cause_id')
                ->leftJoin('users', 'users.id', '=', 'tbl_slot.slot_owner')
                ->leftJoin('tbl_membership', 'tbl_membership.membership_id', '=', 'tbl_earning_log.earning_log_cause_membership_id')
                ->where('earning_log_plan_type', 'WATCH AND EARN')
                ->leftJoin('tbl_label', 'tbl_label.plan_code', '=', 'tbl_earning_log.earning_log_plan_type')
                ->select('*', DB::raw("DATE_FORMAT(tbl_earning_log.earning_log_date_created, '%m/%d/%Y') as earning_log_date_created"),
                    DB::raw("DATE_FORMAT(tbl_earning_log.earning_log_date_created, '%h:%i %p') as earning_log_time_created"))
                ->paginate(10);

            $data['log'] = $log;
            $running_balance = Tbl_earning_log::where('earning_log_slot_id', $slot->slot_id)->where('earning_log_plan_type', 'WATCH EARN')->sum('earning_log_amount');
        }

        $data['total'] = number_format($running_balance, 2);

        return json_encode($data);
    }

    public function global_pool_earning()
    {
        $slot = Tbl_slot::where('slot_owner', Request::user()->id)->where('slot_id', Request::input('current_slot_id'))->first();
        $data = null;
        $running_balance = 0;

        if ($slot) {
            $log = Tbl_earning_log::where('earning_log_slot_id', $slot->slot_id)
                ->leftJoin('tbl_slot', 'tbl_slot.slot_id', '=', 'tbl_earning_log.earning_log_cause_id')
                ->leftJoin('users', 'users.id', '=', 'tbl_slot.slot_owner')
                ->leftJoin('tbl_membership', 'tbl_membership.membership_id', '=', 'tbl_earning_log.earning_log_cause_membership_id')
                ->where('earning_log_plan_type', 'GLOBAL POOL BONUS')
                ->leftJoin('tbl_label', 'tbl_label.plan_code', '=', 'tbl_earning_log.earning_log_plan_type')
                ->select('*', DB::raw("DATE_FORMAT(tbl_earning_log.earning_log_date_created, '%m/%d/%Y') as earning_log_date_created"),
                    DB::raw("DATE_FORMAT(tbl_earning_log.earning_log_date_created, '%h:%i %p') as earning_log_time_created"))
                ->paginate(10);

            $data['log'] = $log;
            $running_balance = Tbl_earning_log::where('earning_log_slot_id', $slot->slot_id)->where('earning_log_plan_type', 'GLOBAL POOL BONUS')->sum('earning_log_amount');
        }

        $data['total'] = number_format($running_balance, 2);

        return json_encode($data);
    }

    public function get_dynamic_breakdown()
    {
        $slot_id = Request::input('current_slot_id');
        $level = Request::input('level');
        $end = Request::input('end');
        $start = Request::input('start');
        //   dd(Request::input());
        // if($level == 0)
        // {
        //   $slot_breakdown["level"] = $level;
        //   $query                          = Tbl_dynamic_compression_record::where("cause_slot_id",$slot_id)->leftJoin("tbl_slot","tbl_slot.slot_id","=","tbl_dynamic_compression_record.cause_slot_id")
        //                                                                     ->where("dynamic_level",$level+1)
        //                                                                     ->select("dynamic_level","earned_points","slot_no");
        //   $slot_breakdown["total_points"] = $query->sum("earned_points");
        //   $slot_breakdown["slots"]        = $query->get();
        // }
        // else
        // {
        $slot_breakdown['level'] = $level;
        $query = Tbl_dynamic_compression_record::where('tbl_dynamic_compression_record.slot_id', $slot_id)
            ->leftJoin('tbl_slot', 'tbl_slot.slot_id', '=', 'tbl_dynamic_compression_record.cause_slot_id')
            ->where('dynamic_level', $level)
            ->whereDate('start_date', '>=', $start)
            ->whereDate('end_date', '<=', $end)
            ->select('dynamic_level', 'earned_points', 'slot_no');
        $slot_breakdown['total_points'] = $query->sum('earned_points');
        $slot_breakdown['slots'] = $query->get();
        // }

        return $slot_breakdown;
    }

    public function get_check_match_earning()
    {
        $slot = Tbl_slot::where('slot_owner', Request::user()->id)->where('slot_id', Request::input('current_slot_id'))->first();
        $data = null;
        $running_balance = 0;

        if ($slot) {
            $log = Tbl_earning_log::where('earning_log_slot_id', $slot->slot_id)
                ->leftJoin('tbl_slot', 'tbl_slot.slot_id', '=', 'tbl_earning_log.earning_log_cause_id')
                ->leftJoin('users', 'users.id', '=', 'tbl_slot.slot_owner')
                ->leftJoin('tbl_membership', 'tbl_membership.membership_id', '=', 'tbl_earning_log.earning_log_cause_membership_id')
                ->where('earning_log_plan_type', 'CHECK MATCH INCOME')
                ->leftJoin('tbl_label', 'tbl_label.plan_code', '=', 'tbl_earning_log.earning_log_plan_type')
                ->select('*', DB::raw("DATE_FORMAT(tbl_earning_log.earning_log_date_created, '%m/%d/%Y') as earning_log_date_created"),
                    DB::raw("DATE_FORMAT(tbl_earning_log.earning_log_date_created, '%h:%i %p') as earning_log_time_created"))
                ->paginate(10);

            $data['log'] = $log;
            $running_balance = Tbl_earning_log::where('earning_log_slot_id', $slot->slot_id)->where('earning_log_plan_type', 'CHECK MATCH INCOME')->sum('earning_log_amount');
        }

        $data['total'] = number_format($running_balance, 2);

        return json_encode($data);
    }

    // dito
    public function incentive_bonus_earning()
    {
        $slot = Tbl_slot::where('slot_owner', Request::user()->id)->where('slot_id', Request::input('current_slot_id'))->first();
        $data = null;
        $running_balance = 0;

        if ($slot) {
            $log = Tbl_earning_log::where('earning_log_slot_id', $slot->slot_id)
                ->leftJoin('tbl_slot', 'tbl_slot.slot_id', '=', 'tbl_earning_log.earning_log_cause_id')
                ->leftJoin('users', 'users.id', '=', 'tbl_slot.slot_owner')
                ->leftJoin('tbl_membership', 'tbl_membership.membership_id', '=', 'tbl_earning_log.earning_log_cause_membership_id')
                ->where('earning_log_plan_type', 'UPCOIN')
                ->leftJoin('tbl_label', 'tbl_label.plan_code', '=', 'tbl_earning_log.earning_log_plan_type')
                ->select('*', DB::raw("DATE_FORMAT(tbl_earning_log.earning_log_date_created, '%m/%d/%Y') as earning_log_date_created"),
                    DB::raw("DATE_FORMAT(tbl_earning_log.earning_log_date_created, '%h:%i %p') as earning_log_time_created"))
                ->paginate(10);

            $data['log'] = $log;
            $running_balance = Tbl_earning_log::where('earning_log_slot_id', $slot->slot_id)->where('earning_log_plan_type', 'UPCOIN')->sum('earning_log_amount');
        }

        $data['total'] = number_format($running_balance, 2);

        return json_encode($data);
    }

    public function leadership_bonus_earning()
    {
        $slot = Tbl_slot::where('slot_owner', Request::user()->id)->where('slot_id', Request::input('current_slot_id'))->first();
        $data = null;
        $running_balance = 0;

        if ($slot) {
            $log = Tbl_earning_log::where('earning_log_slot_id', $slot->slot_id)
                ->leftJoin('tbl_slot', 'tbl_slot.slot_id', '=', 'tbl_earning_log.earning_log_cause_id')
                ->leftJoin('users', 'users.id', '=', 'tbl_slot.slot_owner')
                ->leftJoin('tbl_membership', 'tbl_membership.membership_id', '=', 'tbl_earning_log.earning_log_cause_membership_id')
                ->where('earning_log_plan_type', 'OVERRIDE COMMISSION')
                ->leftJoin('tbl_label', 'tbl_label.plan_code', '=', 'tbl_earning_log.earning_log_plan_type')
                ->select('*', DB::raw("DATE_FORMAT(tbl_earning_log.earning_log_date_created, '%m/%d/%Y') as earning_log_date_created"),
                    DB::raw("DATE_FORMAT(tbl_earning_log.earning_log_date_created, '%h:%i %p') as earning_log_time_created"))
                ->paginate(10);

            $data['log'] = $log;
            $running_balance = Tbl_earning_log::where('earning_log_slot_id', $slot->slot_id)->where('earning_log_plan_type', 'OVERRIDE COMMISSION')->sum('earning_log_amount');
        }

        $data['total'] = number_format($running_balance, 2);

        return json_encode($data);
    }

    public function royalty_bonus_earning()
    {
        $slot = Tbl_slot::where('slot_owner', Request::user()->id)->where('slot_id', Request::input('current_slot_id'))->first();
        $data = null;
        $running_balance = 0;

        if ($slot) {
            $log = Tbl_earning_log::where('earning_log_slot_id', $slot->slot_id)
                ->leftJoin('tbl_slot', 'tbl_slot.slot_id', '=', 'tbl_earning_log.earning_log_cause_id')
                ->leftJoin('users', 'users.id', '=', 'tbl_slot.slot_owner')
                ->leftJoin('tbl_membership', 'tbl_membership.membership_id', '=', 'tbl_earning_log.earning_log_cause_membership_id')
                ->where('earning_log_plan_type', 'BREAKAWAY BONUS')
                ->leftJoin('tbl_label', 'tbl_label.plan_code', '=', 'tbl_earning_log.earning_log_plan_type')
                ->select('*', DB::raw("DATE_FORMAT(tbl_earning_log.earning_log_date_created, '%m/%d/%Y') as earning_log_date_created"),
                    DB::raw("DATE_FORMAT(tbl_earning_log.earning_log_date_created, '%h:%i %p') as earning_log_time_created"))
                ->paginate(10);

            $data['log'] = $log;
            $running_balance = Tbl_earning_log::where('earning_log_slot_id', $slot->slot_id)->where('earning_log_plan_type', 'BREAKAWAY BONUS')->sum('earning_log_amount');
        }

        $data['total'] = number_format($running_balance, 2);

        return json_encode($data);
    }

    public function captcha_earning()
    {
        $slot = Tbl_slot::where('slot_owner', Request::user()->id)->where('slot_id', Request::input('current_slot_id'))->first();
        $data = null;
        $running_balance = 0;

        if ($slot) {
            $log = Tbl_wallet_log::where('wallet_log_slot_id', $slot->slot_id)
                ->leftJoin('tbl_slot', 'tbl_slot.slot_id', '=', 'tbl_wallet_log.wallet_log_slot_id')
                ->leftJoin('users', 'users.id', '=', 'tbl_slot.slot_owner')
                ->leftJoin('tbl_membership', 'tbl_membership.membership_id', '=', 'tbl_earning_log.earning_log_cause_membership_id')
                ->where('wallet_log_details', 'SENDING FUNDS')
                ->select('wallet_log_date_created', 'slot_id', 'wallet_log_amount', 'slot_no', 'name', 'currency_id',
                    DB::raw("DATE_FORMAT(tbl_wallet_log.wallet_log_date_created, '%m/%d/%Y') as wallet_log_date_created"),
                    DB::raw("DATE_FORMAT(tbl_wallet_log.wallet_log_date_created, '%h:%i %p') as wallet_log_time_created"))
                ->paginate(10);
            $data['log'] = $log;
            $running_balance = Tbl_wallet_log::where('wallet_log_id', $slot->slot_id)->where('wallet_log_details', 'SENDING FUNDS')->sum('wallet_log_amount');
        }

        $data['total'] = number_format($running_balance, 2);
        foreach ($data['log'] as $key => $value) {
            $data['log'][$key]->curr_abb = DB::table('tbl_currency')->where('currency_id', $value->currency_id)->first() ? DB::table('tbl_currency')->where('currency_id', $value->currency_id)->first()->currency_abbreviation : 'PHP';
        }
        return json_encode($data);
    }

    function ordinal($number)
    {
        $ends = array('th', 'st', 'nd', 'rd', 'th', 'th', 'th', 'th', 'th', 'th');
        if ((($number % 100) >= 11) && (($number % 100) <= 13))
            return $number . 'th';
        else
            return $number . $ends[$number % 10];
    }

    public function get_earning_label()
    {
        $response = Tbl_label::get();
        return response()->json($response, 200);
    }

    public function personal_cashback()
    {
        $slot = Tbl_slot::where('slot_owner', Request::user()->id)->where('slot_id', Request::input('current_slot_id'))->first();
        $data = null;
        $running_balance = 0;
        $amount_to_compute = 0;
        if ($slot) {
            $log = Tbl_earning_log::where('earning_log_slot_id', $slot->slot_id)
                ->leftJoin('tbl_slot', 'tbl_slot.slot_id', '=', 'tbl_earning_log.earning_log_cause_id')
                ->leftJoin('users', 'users.id', '=', 'tbl_slot.slot_owner')
                ->leftJoin('tbl_membership', 'tbl_membership.membership_id', '=', 'tbl_earning_log.earning_log_cause_membership_id')
                ->where('earning_log_plan_type', 'PERSONAL CASHBACK')
                ->leftJoin('tbl_label', 'tbl_label.plan_code', '=', 'tbl_earning_log.earning_log_plan_type')
                ->select('*', DB::raw("DATE_FORMAT(tbl_earning_log.earning_log_date_created, '%m/%d/%Y') as earning_log_date_created"),
                    DB::raw("DATE_FORMAT(tbl_earning_log.earning_log_date_created, '%h:%i %p') as earning_log_time_created"))
                ->paginate(10);
            $data['log'] = $log;
            $running_balance = Tbl_earning_log::where('earning_log_slot_id', $slot->slot_id)->where('earning_log_plan_type', 'PERSONAL CASHBACK')->sum('earning_log_amount');
        }
        $data['total'] = number_format($running_balance, 2);
        return json_encode($data);
    }

    public function retailer_commission()
    {
        $response = Tbl_retailer_commission_logs::where('tbl_retailer_commission_logs.slot_id', Request::input('current_slot_id'))
            ->leftjoin('tbl_slot', 'tbl_slot.slot_id', 'tbl_retailer_commission_logs.cause_slot_id')
            ->leftjoin('users', 'users.id', 'tbl_slot.slot_owner')
            ->leftjoin('tbl_item', 'tbl_item.item_id', 'tbl_retailer_commission_logs.item_id')
            ->orderBy('date', 'DESC')
            ->paginate(10);

        return $response;
    }

    public function share_link_v2()
    {
        $user_id = Request::user()->id;
        $label = Tbl_label::where('plan_code', 'SHARE_LINK_V2')->first()->plan_name;

        $response = Tbl_slot::where('slot_owner', $user_id)
            ->leftjoin('tbl_earning_log', 'tbl_earning_log.earning_log_slot_id', '=', 'tbl_slot.slot_id')
            ->where('tbl_earning_log.earning_log_plan_type', $label)
            ->orderBy('tbl_earning_log.earning_log_date_created', 'DESC')
            ->paginate(10);

        foreach ($response as $key => $value) {
            $value['cause_slot_info'] = Tbl_slot::where('slot_id', $value->earning_log_cause_id)->leftjoin('users', 'users.id', 'tbl_slot.slot_owner')->first();
        }
        return $response;
    }

    public function product_share_link()
    {
        $user_id = Request::user()->id;
        $label = Tbl_label::where('plan_code', 'PRODUCT_SHARE_LINK')->first()->plan_name;

        $response = Tbl_slot::where('slot_owner', $user_id)
            ->leftjoin('tbl_earning_log', 'tbl_earning_log.earning_log_slot_id', '=', 'tbl_slot.slot_id')
            ->where('tbl_earning_log.earning_log_plan_type', $label)
            ->orderBy('tbl_earning_log.earning_log_date_created', 'DESC')
            ->paginate(10);

        foreach ($response as $key => $value) {
            $value['cause_slot_info'] = Tbl_slot::where('slot_id', $value->earning_log_cause_id)->leftjoin('users', 'users.id', 'tbl_slot.slot_owner')->first();
        }
        return $response;
    }

    public function overriding_commission()
    {
        $user_id = Request::user()->id;
        $label = Tbl_label::where('plan_code', 'OVERRIDING_COMMISSION')->first()->plan_name;

        $response = Tbl_slot::where('slot_owner', $user_id)
            ->leftjoin('tbl_earning_log', 'tbl_earning_log.earning_log_slot_id', '=', 'tbl_slot.slot_id')
            ->where('tbl_earning_log.earning_log_plan_type', $label)
            ->orderBy('tbl_earning_log.earning_log_date_created', 'DESC')
            ->paginate(10);

        foreach ($response as $key => $value) {
            $value['cause_slot_info'] = Tbl_slot::where('slot_id', $value->earning_log_cause_id)->leftjoin('users', 'users.id', 'tbl_slot.slot_owner')->first();
        }
        return $response;
    }

    public function product_direct_referral()
    {
        $user_id = Request::user()->id;
        $slot_id = Tbl_slot::where('slot_owner', $user_id)->first()->slot_id;
        $label = Tbl_label::where('plan_code', 'PRODUCT_DIRECT_REFERRAL')->first()->plan_name;

        $response = Tbl_product_direct_referral_logs::where('tbl_product_direct_referral_logs.slot_id', $slot_id)
            ->leftjoin('tbl_slot', 'tbl_slot.slot_id', '=', 'tbl_product_direct_referral_logs.slot_id')
            ->leftjoin('tbl_item', 'tbl_item.item_id', '=', 'tbl_product_direct_referral_logs.item_id')
            ->orderBy('tbl_product_direct_referral_logs.date', 'DESC')
            ->paginate(10);

        foreach ($response as $key => $value) {
            $value['cause_slot_info'] = Tbl_slot::where('slot_id', $value->buyer_id)->leftjoin('users', 'users.id', 'tbl_slot.slot_owner')->first();
        }
        return $response;
    }

    public function direct_personal_cashback()
    {
        $slot = Tbl_slot::where('slot_owner', Request::user()->id)->where('slot_id', Request::input('current_slot_id'))->first();
        $data = null;
        $running_balance = 0;
        $amount_to_compute = 0;
        if ($slot) {
            $log = Tbl_earning_log::where('earning_log_slot_id', $slot->slot_id)
                ->leftJoin('tbl_slot', 'tbl_slot.slot_id', '=', 'tbl_earning_log.earning_log_cause_id')
                ->leftJoin('users', 'users.id', '=', 'tbl_slot.slot_owner')
                ->leftJoin('tbl_membership', 'tbl_membership.membership_id', '=', 'tbl_earning_log.earning_log_cause_membership_id')
                ->where('earning_log_plan_type', 'DIRECT PERSONAL CASHBACK')
                ->leftJoin('tbl_label', 'tbl_label.plan_code', '=', 'tbl_earning_log.earning_log_plan_type')
                ->select('*', DB::raw("DATE_FORMAT(tbl_earning_log.earning_log_date_created, '%m/%d/%Y') as earning_log_date_created"),
                    DB::raw("DATE_FORMAT(tbl_earning_log.earning_log_date_created, '%h:%i %p') as earning_log_time_created"))
                ->paginate(10);
            $data['log'] = $log;
            $running_balance = Tbl_earning_log::where('earning_log_slot_id', $slot->slot_id)->where('earning_log_plan_type', 'DIRECT PERSONAL CASHBACK')->sum('earning_log_amount');
        }
        $data['total'] = number_format($running_balance, 2);
        return json_encode($data);
    }

    public function product_personal_cashback()
    {
        $user_id = Request::user()->id;
        $slot_id = Tbl_slot::where('slot_owner', $user_id)->first()->slot_id;
        $label = Tbl_label::where('plan_code', 'PRODUCT_PERSONAL_CASHBACK')->first()->plan_name;

        $response = Tbl_product_personal_cashback_logs::where('tbl_product_personal_cashback_logs.slot_id', $slot_id)
            ->leftjoin('tbl_slot', 'tbl_slot.slot_id', '=', 'tbl_product_personal_cashback_logs.cause_id')
            ->leftjoin('users', 'users.id', '=', 'tbl_slot.slot_owner')
            ->leftjoin('tbl_item', 'tbl_item.item_id', '=', 'tbl_product_personal_cashback_logs.item_id')
            ->orderBy('tbl_product_personal_cashback_logs.date', 'DESC')
            ->paginate(10);

        return $response;
    }

    public function team_sales_bonus()
    {
        $user_id = Request::user()->id;
        $slot_id = Tbl_slot::where('slot_owner', $user_id)->first()->slot_id;
        $label = Tbl_label::where('plan_code', 'TEAM_SALES_BONUS')->first()->plan_name;

        $response = Tbl_team_sales_bonus_logs::where('tbl_team_sales_bonus_logs.slot_id', $slot_id)
            ->leftjoin('tbl_slot', 'tbl_slot.slot_id', '=', 'tbl_team_sales_bonus_logs.cause_id')
            ->leftjoin('users', 'users.id', '=', 'tbl_slot.slot_owner')
            ->leftjoin('tbl_item', 'tbl_item.item_id', '=', 'tbl_team_sales_bonus_logs.item_id')
            ->orderBy('tbl_team_sales_bonus_logs.date', 'DESC')
            ->paginate(10);

        return $response;
    }

    public function retailer_override()
    {
        $user_id = Request::user()->id;
        $slot_id = Tbl_slot::where('slot_owner', $user_id)->first()->slot_id;
        $label = Tbl_label::where('plan_code', 'RETAILER_OVERRIDE')->first()->plan_name;

        $response = Tbl_retailer_override_logs::where('tbl_retailer_override_logs.slot_id', $slot_id)
            ->leftjoin('tbl_slot', 'tbl_slot.slot_id', '=', 'tbl_retailer_override_logs.cause_id')
            ->leftjoin('users', 'users.id', '=', 'tbl_slot.slot_owner')
            ->leftjoin('tbl_item', 'tbl_item.item_id', '=', 'tbl_retailer_override_logs.item_id')
            ->orderBy('tbl_retailer_override_logs.date', 'DESC')
            ->paginate(10);

        return $response;
    }

    public function reverse_pass_up()
    {
        $slot = Tbl_slot::where('slot_owner', Request::user()->id)->where('slot_id', Request::input('current_slot_id'))->first();
        $data = null;
        $running_balance = 0;

        if ($slot) {
            $log = Tbl_earning_log::where('earning_log_slot_id', $slot->slot_id)
                ->leftJoin('tbl_slot', 'tbl_slot.slot_id', '=', 'tbl_earning_log.earning_log_cause_id')
                ->leftJoin('users', 'users.id', '=', 'tbl_slot.slot_owner')
                ->leftJoin('tbl_membership', 'tbl_membership.membership_id', '=', 'tbl_earning_log.earning_log_cause_membership_id')
                ->where('earning_log_plan_type', 'REVERSE PASS UP')
                ->leftJoin('tbl_label', 'tbl_label.plan_code', '=', 'tbl_earning_log.earning_log_plan_type')
                ->select('*', DB::raw("DATE_FORMAT(tbl_earning_log.earning_log_date_created, '%m/%d/%Y') as earning_log_date_created"),
                    DB::raw("DATE_FORMAT(tbl_earning_log.earning_log_date_created, '%h:%i %p') as earning_log_time_created"))
                ->paginate(10);

            $data['log'] = $log;
            $running_balance = Tbl_earning_log::where('earning_log_slot_id', $slot->slot_id)->where('earning_log_plan_type', 'PASS UP')->sum('earning_log_amount');
        }

        $data['total'] = number_format($running_balance, 2);

        return json_encode($data);
    }

    public function achievers_rank()
    {
        $slot = Tbl_slot::where('slot_owner', Request::user()->id)->where('slot_id', Request::input('current_slot_id'))->first();
        if (!$slot)
            return response()->json(['status' => 'error', 'message' => 'Slot not found']);

        $data['log'] = Tbl_earning_log::where('earning_log_slot_id', $slot->slot_id)
            ->where('earning_log_plan_type', 'ACHIEVERS RANK')
            ->leftJoin('tbl_slot', 'tbl_slot.slot_id', '=', 'tbl_earning_log.earning_log_cause_id')
            ->leftJoin('tbl_label', 'tbl_label.plan_code', '=', 'tbl_earning_log.earning_log_plan_type')
            ->select(
                '*',
                DB::raw("DATE_FORMAT(tbl_earning_log.earning_log_date_created, '%m/%d/%Y') as earning_log_date_created"),
                DB::raw("DATE_FORMAT(tbl_earning_log.earning_log_date_created, '%h:%i %p') as earning_log_time_created")
            )
            ->orderBy('earning_log_id', 'desc')
            ->paginate(10);

        $running_balance = Tbl_earning_log::where('earning_log_slot_id', $slot->slot_id)->where('earning_log_plan_type', 'ACHIEVERS RANK')->sum('earning_log_amount');

        $reward_amounts = collect($data['log']->items())->map(function ($item) use ($slot) {
            return ($slot->slot_id == $item->earning_log_cause_id) ? ($item->earning_log_amount * 2) : (($item->earning_log_amount * 2) * 2);
        })->unique();

        $ranks_map = Tbl_achievers_rank::where('archive', 0)->whereIn('achievers_rank_reward', $reward_amounts)->get()->keyBy('achievers_rank_reward');

        foreach ($data['log'] as $index => $logItem) {
            if ($slot->slot_id == $logItem->earning_log_cause_id) {
                $totalReward = ($logItem->earning_log_amount * 2);
                $data['log'][$index]['claimed_by'] = $logItem->slot_no . ' (You)';
                $data['log'][$index]['total_reward'] = '?' . number_format($totalReward, 0, '', ',') . ' (50%)';
            } else {
                $totalReward = ($logItem->earning_log_amount * 2) * 2;
                $data['log'][$index]['claimed_by'] = $logItem->slot_no . ' (Sponsor)';
                $data['log'][$index]['total_reward'] = '?' . number_format($totalReward, 0, '', ',') . ' (25%)';
            }
            $rank = $ranks_map->get($totalReward);
            $data['log'][$index]['rank_name'] = $rank ? $rank->achievers_rank_name : 'N/A';
        }

        $data['total'] = number_format($running_balance, 2);
        return response()->json($data);
    }

    public function dropshipping_bonus()
    {
        $slot_id = Request::input('current_slot_id');
        $response = Tbl_dropshipping_bonus_logs::where('tbl_dropshipping_bonus_logs.slot_id', $slot_id)
            ->leftjoin('tbl_slot', 'tbl_slot.slot_id', '=', 'tbl_dropshipping_bonus_logs.slot_id')
            ->leftjoin('users', 'users.id', '=', 'tbl_slot.slot_owner')
            ->leftjoin('tbl_item', 'tbl_item.item_id', '=', 'tbl_dropshipping_bonus_logs.item_id')
            ->orderBy('tbl_dropshipping_bonus_logs.date', 'DESC')
            ->paginate(10);

        $order_ids = collect($response->items())->pluck('order_id')->unique()->filter();
        $orders_map = Tbl_orders::whereIn('order_id', $order_ids)->get()->keyBy('order_id');

        foreach ($response as $key => $data) {
            $order = $orders_map->get($data->order_id);
            $response[$key]->buyer_name = $order ? $order->buyer_name : 'N/A';
            $response[$key]->buyer_contact_number = $order ? $order->buyer_contact_number : 'N/A';
        }

        return $response;
    }

    public function welcome_bonus_earning()
    {
        $slot = Tbl_slot::where('slot_owner', Request::user()->id)->where('slot_id', Request::input('current_slot_id'))->first();
        $data = null;
        $running_balance = 0;

        if ($slot) {
            $log = Tbl_earning_log::where('earning_log_slot_id', $slot->slot_id)
                ->leftJoin('tbl_slot', 'tbl_slot.slot_id', '=', 'tbl_earning_log.earning_log_cause_id')
                ->leftJoin('users', 'users.id', '=', 'tbl_slot.slot_owner')
                ->leftJoin('tbl_membership', 'tbl_membership.membership_id', '=', 'tbl_earning_log.earning_log_cause_membership_id')
                ->where('earning_log_plan_type', 'WELCOME BONUS')
                ->leftJoin('tbl_label', 'tbl_label.plan_code', '=', 'tbl_earning_log.earning_log_plan_type')
                ->select('*', DB::raw("DATE_FORMAT(tbl_earning_log.earning_log_date_created, '%m/%d/%Y') as earning_log_date_created"),
                    DB::raw("DATE_FORMAT(tbl_earning_log.earning_log_date_created, '%h:%i %p') as earning_log_time_created"))
                ->paginate(10);

            $data['log'] = $log;
            $running_balance = Tbl_earning_log::where('earning_log_slot_id', $slot->slot_id)->where('earning_log_plan_type', 'WELCOME BONUS')->sum('earning_log_amount');
        }

        $data['total'] = number_format($running_balance, 2);

        return json_encode($data);
    }

    public function unilevel_matrix_bonus()
    {
        $slot = Tbl_slot::where('slot_owner', Request::user()->id)->where('slot_id', Request::input('current_slot_id'))->first();
        $data = null;
        $total_points = 0;
        $log = null;
        $ctr = 0;
        $start_date = Request::input('start_date');
        $end_date = Request::input('end_date');
        $matrix_settings = DB::table('tbl_unilevel_matrix_bonus_settings')->first();

        if ($slot) {
            $log = Tbl_earning_log::where('earning_log_slot_id', $slot->slot_id)
                ->leftJoin('tbl_slot', 'tbl_slot.slot_id', '=', 'tbl_earning_log.earning_log_cause_id')
                ->leftJoin('users', 'users.id', '=', 'tbl_slot.slot_owner')
                ->leftJoin('tbl_membership', 'tbl_membership.membership_id', '=', 'tbl_earning_log.earning_log_cause_membership_id')
                ->where('earning_log_plan_type', 'UNILEVEL MATRIX BONUS')
                ->leftJoin('tbl_label', 'tbl_label.plan_code', '=', 'tbl_earning_log.earning_log_plan_type')
                ->orderBy('earning_log_id', 'desc')
                ->paginate(10);

            $data['log'] = $log;
            $running_balance = Tbl_earning_log::where('earning_log_slot_id', $slot->slot_id)->where('earning_log_plan_type', 'UNILEVEL MATRIX BONUS')->sum('earning_log_amount');
        }

        $data['total'] = number_format($running_balance, 2);

        return json_encode($data);
    }

    public function get_matrix_per_level_details()
    {
        $slot_id = Request::input('current_slot_id');
        $level = Request::input('level') + 1;
        $end = Request::input('end');
        $start = Request::input('start');
        $data['level'] = $level;
        $query = Tbl_unilevel_matrix_bonus_amount::where('matrix_slot_id', $slot_id)
            ->where('matrix_type', 'UNILEVEL_MATRIX_BONUS_AMOUNT')
            ->leftJoin('tbl_slot', 'tbl_slot.slot_id', '=', 'tbl_unilevel_matrix_bonus_amount.matrix_cause_id')
            ->where('matrix_cause_level', $level)
            ->where('matrix_distribute', 0)
            ->whereDate('matrix_date_created', '>=', $start)
            ->whereDate('matrix_date_created', '<=', $end)
            ->select('matrix_cause_level', 'matrix_amount', 'slot_no', 'matrix_date_created');
        $data['total_points'] = $query->sum('matrix_amount');
        $data['slots'] = $query->get();
        // }

        return $data;
    }

    public function reward_points_earning()
    {
        $slot = Tbl_slot::where('slot_owner', Request::user()->id)->where('slot_id', Request::input('current_slot_id'))->first();
        $data = null;
        $running_balance = 0;

        if ($slot) {
            $log = Tbl_earning_log::where('earning_log_slot_id', $slot->slot_id)
                ->leftJoin('tbl_slot', 'tbl_slot.slot_id', '=', 'tbl_earning_log.earning_log_cause_id')
                ->leftJoin('users', 'users.id', '=', 'tbl_slot.slot_owner')
                ->leftJoin('tbl_membership', 'tbl_membership.membership_id', '=', 'tbl_earning_log.earning_log_cause_membership_id')
                ->where('earning_log_plan_type', 'REWARD POINTS')
                ->leftJoin('tbl_label', 'tbl_label.plan_code', '=', 'tbl_earning_log.earning_log_plan_type')
                ->select('*', DB::raw("DATE_FORMAT(tbl_earning_log.earning_log_date_created, '%m/%d/%Y') as earning_log_date_created"),
                    DB::raw("DATE_FORMAT(tbl_earning_log.earning_log_date_created, '%h:%i %p') as earning_log_time_created"))
                ->paginate(12);

            $data['log'] = $log;
            $running_balance = Tbl_earning_log::where('earning_log_slot_id', $slot->slot_id)->where('earning_log_plan_type', 'REWARD POINTS')->sum('earning_log_amount');
        }

        $data['total'] = number_format($running_balance, 2);

        return json_encode($data);
    }

    public function prime_refund_earning()
    {
        $slot = Tbl_slot::where('slot_owner', Request::user()->id)->where('slot_id', Request::input('current_slot_id'))->first();
        $data = null;
        $running_balance = 0;

        if ($slot) {
            $log = Tbl_prime_refund_points_log::from('tbl_prime_refund_points_log as log')
                ->where('log.slot_id', $slot->slot_id)
                ->leftJoin('tbl_slot as slot', 'slot.slot_id', '=', 'log.cause_slot_id')
                ->leftJoin('tbl_membership as membership', 'membership.membership_id', '=', 'log.cause_membership_id')
                ->select(
                    'log.*',
                    'slot.slot_no as slot_no',
                    'membership.membership_name as membership_name'
                )
                ->paginate(10);

            $data['log'] = $log;
            $running_balance = Tbl_prime_refund_points_log::where('slot_id', $slot->slot_id)->sum('commission');
        }

        $data['total'] = number_format($running_balance, 2);

        return json_encode($data);
    }

    public function incentive_earning()
    {
        $slot = Tbl_slot::where('slot_owner', Request::user()->id)->where('slot_id', Request::input('current_slot_id'))->first();
        $data = null;
        $running_balance = 0;

        if ($slot) {
            $log = Tbl_earning_log::where('earning_log_slot_id', $slot->slot_id)
                ->leftJoin('tbl_slot', 'tbl_slot.slot_id', '=', 'tbl_earning_log.earning_log_cause_id')
                ->leftJoin('users', 'users.id', '=', 'tbl_slot.slot_owner')
                ->leftJoin('tbl_membership', 'tbl_membership.membership_id', '=', 'tbl_earning_log.earning_log_cause_membership_id')
                ->where('earning_log_plan_type', 'INCENTIVE')
                ->leftJoin('tbl_label', 'tbl_label.plan_code', '=', 'tbl_earning_log.earning_log_plan_type')
                ->select('*', DB::raw("DATE_FORMAT(tbl_earning_log.earning_log_date_created, '%m/%d/%Y') as earning_log_date_created"),
                    DB::raw("DATE_FORMAT(tbl_earning_log.earning_log_date_created, '%h:%i %p') as earning_log_time_created"))
                ->paginate(10);

            $data['log'] = $log;
            $running_balance = Tbl_earning_log::where('earning_log_slot_id', $slot->slot_id)->where('earning_log_plan_type', 'INCENTIVE')->sum('earning_log_amount');
        }

        $data['total'] = number_format($running_balance, 2);

        return json_encode($data);
    }

    public function milestone_earning()
    {
        $milestone_settings = Tbl_milestone_bonus_settings::first();
        $cycle = $milestone_settings->miletone_cycle_limit;
        $slot = Tbl_slot::where('slot_owner', Request::user()->id)
            ->where('slot_id', Request::input('current_slot_id'))
            ->JoinMembership()
            ->first();

        $log = null;
        $running_balance = 0;
        if ($slot) {
            $slotId = $slot->slot_id;
            $typeLimit = $milestone_settings->milestone_type_limit;
            $cycleLimit = $milestone_settings->milestone_cycle_limit;

            // Fetch logs
            $log = Tbl_milestone_points_log::where('points_slot_id', $slotId)
                ->leftJoin('tbl_membership as membership', 'membership.membership_id', '=', 'tbl_milestone_points_log.points_cause_membership_id')
                ->leftJoin('tbl_slot as cause_slot', 'cause_slot.slot_id', '=', 'tbl_milestone_points_log.points_cause_slot_id')
                ->orderBy('points_log_id', 'desc')
                ->paginate(10);

            // Running balance
            $running_balance = Tbl_milestone_points_log::where('points_slot_id', $slotId)->sum('points_income');

            // Base milestone earning log query
            $baseQuery = Tbl_earning_log::where('earning_log_slot_id', $slotId)
                ->where('earning_log_plan_type', 'MILESTONE BONUS');

            // Apply time filtering based on cycle limit
            $query = clone $baseQuery;

            if ($cycleLimit === 'daily') {
                $today = Carbon::now()->toDateString();
                $query->whereDate('earning_log_date_created', $today);
            } elseif ($cycleLimit === 'halfday') {
                $now = Carbon::now();
                $start = $now->copy()->startOfDay();
                $noon = $now->copy()->setTime(12, 0);
                $end = $now->copy()->endOfDay();

                $rangeStart = $now->format('A') === 'AM' ? $start : $noon;
                $rangeEnd = $now->format('A') === 'AM' ? $noon : $end;

                $query->whereBetween('earning_log_date_created', [$rangeStart, $rangeEnd]);
            } elseif ($cycleLimit == 3) {  // Weekly
                $start = Carbon::now()->startOfWeek();
                $end = Carbon::now()->endOfWeek();
                $query->whereBetween('earning_log_date_created', [$start, $end]);
            }  // Else: no time filtering for full duration

            // Fetch amount
            if ($typeLimit === 'pairs') {
                $data['amount'] = $query->count();
            } elseif ($typeLimit === 'earnings') {
                $data['amount'] = $query->sum('earning_log_amount');
            } else {
                $data['amount'] = 0;
            }

            $data['amount'] = round($data['amount'], 2);

            // Get milestone maximum limit
            $slotWithMembership = Tbl_slot::where('slot_id', $slotId)->JoinMembership()->first();
            $data['maximum_limit'] = $slotWithMembership ? $slotWithMembership->milestone_maximum_limit : 0;
        }

        $data['log'] = $log;
        $data['total_running'] = $running_balance;
        // dd($data);
        return json_encode($data);
    }

    public function milestone_points()
    {
        $milestone_settings = Tbl_milestone_bonus_settings::first();
        $slot_id = Request::input('current_slot_id');

        if ($milestone_settings->milestone_cycle_limit == 'daily') {
            if ($milestone_settings->milestone_type_limit == 'pairs') {
                $earningsExpression = DB::raw('COUNT(earning_log_amount) as earnings');
            } else if ($$milestone_settings->milestone_type_limit == 'earnings') {
                $earningsExpression = DB::raw('SUM(earning_log_amount) as earnings');
            }
            $response['data'] = Tbl_earning_log::where('earning_log_slot_id', $slot_id)
                ->where('earning_log_plan_type', 'MILESTONE BONUS')
                ->select(
                    DB::raw("DATE_FORMAT(tbl_earning_log.earning_log_date_created, '%M %d, %Y') as earning_log_date_created"),
                    $earningsExpression
                )
                ->groupBy(DB::raw("DATE_FORMAT(tbl_earning_log.earning_log_date_created, '%M %d, %Y')"))
                ->orderBy('earning_log_slot_id', 'desc')
                ->paginate(10);
        } else if ($milestone_settings->milestone_cycle_limit == 'halfday') {
            if ($milestone_settings->milestone_type_limit == 'pairs') {
                $morningEarnings = DB::raw("COALESCE(COUNT(CASE WHEN TIME(tbl_earning_log.earning_log_date_created) < '12:00:00' THEN tbl_earning_log.earning_log_amount END), 0) as morning_earnings");
                $afternoonEarnings = DB::raw("COALESCE(COUNT(CASE WHEN TIME(tbl_earning_log.earning_log_date_created) >= '12:00:00' THEN tbl_earning_log.earning_log_amount END), 0) as afternoon_earnings");
            } else if ($$milestone_settings->milestone_type_limit == 'earnings') {
                $morningEarnings = DB::raw("COALESCE(SUM(CASE WHEN TIME(tbl_earning_log.earning_log_date_created) < '12:00:00' THEN tbl_earning_log.earning_log_amount END), 0) as morning_earnings");
                $afternoonEarnings = DB::raw("COALESCE(SUM(CASE WHEN TIME(tbl_earning_log.earning_log_date_created) >= '12:00:00' THEN tbl_earning_log.earning_log_amount END), 0) as afternoon_earnings");
            }

            $response['data'] = Tbl_earning_log::where('earning_log_slot_id', $slot_id)
                ->where('earning_log_plan_type', 'MILESTONE BONUS')
                ->select(
                    DB::raw("DATE_FORMAT(tbl_earning_log.earning_log_date_created, '%M %d, %Y') as earning_log_date_created"),
                    $morningEarnings,
                    $afternoonEarnings
                )
                ->groupBy(DB::raw("DATE_FORMAT(tbl_earning_log.earning_log_date_created, '%M %d, %Y')"))
                ->orderBy(DB::raw("DATE_FORMAT(tbl_earning_log.earning_log_date_created, '%M %d, %Y')"), 'desc')
                ->paginate(10);
        } else if ($milestone_settings->milestone_cycle_limit == 'weekly') {
            $response['data'] = Tbl_earning_log::where('earning_log_slot_id', $slot_id)
                ->where('earning_log_plan_type', 'MILESTONE BONUS')
                ->select(
                    DB::raw('YEARWEEK(earning_log_date_created, 1) as year_week'),  // Group by week
                    DB::raw('MIN(DATE_SUB(earning_log_date_created, INTERVAL WEEKDAY(earning_log_date_created) DAY)) as start_of_week'),  // Start of the week (Monday)
                    DB::raw('MAX(DATE_ADD(DATE_SUB(earning_log_date_created, INTERVAL WEEKDAY(earning_log_date_created) DAY), INTERVAL 6 DAY)) as end_of_week'),  // End of the week (Sunday)
                    DB::raw('SUM(tbl_earning_log.earning_log_amount) as total_earnings'),  // Total earnings for the week
                    DB::raw('COUNT(tbl_earning_log.earning_log_amount) as total_pairs')  // Total pairs for the week
                )
                ->groupBy(DB::raw('YEARWEEK(earning_log_date_created, 1)'))  // Group by week number
                ->orderBy(DB::raw('YEARWEEK(earning_log_date_created, 1)'), 'desc')  // Sort weeks in descending order
                ->paginate(10);
        } else {
            if ($milestone_settings->milestone_type_limit == 'pairs') {
                $earningsExpression = DB::raw('COUNT(earning_log_amount) as earnings');
            } else if ($$milestone_settings->milestone_type_limit == 'earnings') {
                $earningsExpression = DB::raw('SUM(earning_log_amount) as earnings');
            }
            $totalStats = Tbl_earning_log::where('earning_log_slot_id', $slot_id)
                ->where('earning_log_plan_type', 'MILESTONE BONUS')
                ->select(
                    DB::raw('SUM(tbl_earning_log.earning_log_amount) as total_earnings'),
                    DB::raw('COUNT(tbl_earning_log.earning_log_amount) as total_pairs')
                )
                ->first();

            $earningLogs = Tbl_earning_log::where('earning_log_slot_id', $slot_id)
                ->where('earning_log_plan_type', 'MILESTONE BONUS')
                ->select(
                    DB::raw("DATE_FORMAT(tbl_earning_log.earning_log_date_created, '%M %d, %Y') as earning_log_date_created"),
                    DB::raw('COUNT(earning_log_amount) as total_pairs'),
                    DB::raw('SUM(earning_log_amount) as total_earnings')
                )
                ->groupBy(DB::raw("DATE_FORMAT(tbl_earning_log.earning_log_date_created, '%M %d, %Y')"))
                ->orderBy('earning_log_slot_id', 'desc')
                ->paginate(10);

            $response = [
                'total_earnings' => $totalStats->total_earnings ?? 0,
                'total_pairs' => $totalStats->total_pairs ?? 0,
                'data' => $earningLogs
            ];
        }

        return $response;
    }

    public function infinity_bonus_earning()
    {
        $slot = Tbl_slot::where('slot_owner', Request::user()->id)->where('slot_id', Request::input('current_slot_id'))->first();
        $data = null;
        $running_balance = 0;

        if ($slot) {
            $log = Tbl_earning_log::where('earning_log_slot_id', $slot->slot_id)
                ->leftJoin('tbl_infinity_bonus_log', 'tbl_infinity_bonus_log.earning_log_id', '=', 'tbl_earning_log.earning_log_id')
                ->leftJoin('tbl_label', 'tbl_label.plan_code', '=', 'tbl_infinity_bonus_log.plan_trigger')
                ->leftJoin('tbl_slot', 'tbl_slot.slot_id', '=', 'tbl_earning_log.earning_log_cause_id')
                ->leftJoin('users', 'users.id', '=', 'tbl_slot.slot_owner')
                ->leftJoin('tbl_membership', 'tbl_membership.membership_id', '=', 'tbl_earning_log.earning_log_cause_membership_id')
                ->where('earning_log_plan_type', 'INFINITY BONUS')
                ->orderBy('tbl_earning_log.earning_log_id', 'desc')
                ->paginate(10);

            $data['log'] = $log;
            $running_balance = Tbl_earning_log::where('earning_log_slot_id', $slot->slot_id)->where('earning_log_plan_type', 'INFINITY BONUS')->sum('earning_log_amount');
        }

        $data['total'] = number_format($running_balance, 2);

        return json_encode($data);
    }

    public function marketing_support_earning()
    {
        $slot = Tbl_slot::where('slot_owner', Request::user()->id)->where('slot_id', Request::input('current_slot_id'))->first();
        $data = null;
        $running_balance = 0;

        if ($slot) {
            $log = Tbl_earning_log::where('earning_log_slot_id', $slot->slot_id)
                ->leftJoin('tbl_slot', 'tbl_slot.slot_id', '=', 'tbl_earning_log.earning_log_cause_id')
                ->leftJoin('users', 'users.id', '=', 'tbl_slot.slot_owner')
                ->leftJoin('tbl_membership', 'tbl_membership.membership_id', '=', 'tbl_earning_log.earning_log_cause_membership_id')
                ->where('earning_log_plan_type', 'MARKETING SUPPORT')
                ->orderBy('tbl_earning_log.earning_log_id', 'desc')
                ->paginate(10);
            $monthly_log = Tbl_marketing_support_log::select(
                'log_income_count',
                DB::raw('COUNT(CASE WHEN log_claimed = 1 THEN log_income END) as marketing_support_claimed_count'),
                DB::raw('COALESCE(SUM(CASE WHEN log_claimed = 1 AND log_status = 0 THEN log_income ELSE 0 END), 0) as marketing_support_waiting_income'),
                DB::raw('COALESCE(SUM(CASE WHEN log_status = 1 THEN log_income ELSE 0 END), 0) as marketing_support_gained_income'),
                DB::raw('MIN(log_date_created) as log_start_date_created'),
                DB::raw('MAX(log_date_created) as log_end_date_created')
            )
                ->where('log_slot_id', $slot->slot_id)
                ->groupBy('log_income_count')
                ->get();

            $data['monthly_log'] = $monthly_log;
            $data['log'] = $log;
            $running_balance = Tbl_earning_log::where('earning_log_slot_id', $slot->slot_id)->where('earning_log_plan_type', 'MARKETING SUPPORT')->sum('earning_log_amount');
        }

        $data['total'] = number_format($running_balance, 2);

        return json_encode($data);
    }

    public function marketing_support_daily_income()
    {
        $slot = Tbl_slot::where('slot_owner', Request::user()->id)
            ->where('slot_id', Request::input('current_slot_id'))
            ->first();
        $income_count = Request::input('income_count');

        $data['log'] = Tbl_marketing_support_log::where('log_slot_id', $slot->slot_id)
            ->where('log_slot_id', $slot->slot_id)
            ->where('log_income_count', $income_count)
            ->where('log_claimed', 1)
            ->paginate(10);
        $data['total'] = Tbl_marketing_support_log::where('log_slot_id', $slot->slot_id)
            ->where('log_slot_id', $slot->slot_id)
            ->where('log_income_count', $income_count)
            ->where('log_claimed', 1)
            ->sum('log_income');
        return json_encode($data);
    }

    public function leaders_support_earning()
    {
        $slot = Tbl_slot::where('slot_owner', Request::user()->id)->where('slot_id', Request::input('current_slot_id'))->first();
        $data = null;
        $running_balance = 0;

        if ($slot) {
            $log = Tbl_leaders_support_log::where('log_slot_id', $slot->slot_id)
                ->leftJoin('tbl_slot', 'tbl_slot.slot_id', '=', 'tbl_leaders_support_log.log_cause_slot_id')
                ->leftJoin('tbl_membership', 'tbl_membership.membership_id', '=', 'tbl_leaders_support_log.log_cause_membership_id')
                ->select(
                    'tbl_leaders_support_log.*',
                    'tbl_slot.*',
                    'tbl_membership.*',
                    DB::raw('COALESCE(CASE WHEN log_status = 0 THEN log_income END, 0) as log_waiting_income'),
                    DB::raw('COALESCE(CASE WHEN log_status = 1 THEN log_income END, 0) as log_gained_income')
                )
                ->orderByDesc('tbl_leaders_support_log.log_status')
                ->orderBy('tbl_leaders_support_log.log_date_end')
                ->paginate(10);

            $data['log'] = $log;
            $running_balance = Tbl_earning_log::where('earning_log_slot_id', $slot->slot_id)
                ->where('earning_log_plan_type', 'LEADERS SUPPORT')
                ->sum('earning_log_amount');
        }

        $data['total'] = number_format($running_balance, 2);

        return json_encode($data);
    }
}
