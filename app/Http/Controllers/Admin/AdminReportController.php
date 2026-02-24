<?php
namespace App\Http\Controllers\Admin;

use App\Globals\Branch;
use App\Globals\Get_plan;
use App\Globals\Slot;
use App\Models\Tbl_achievers_rank;
use App\Models\Tbl_achievers_rank_list;
use App\Models\Tbl_address;
use App\Models\Tbl_adjust_wallet_log;
use App\Models\Tbl_binary_points;
use App\Models\Tbl_branch;
use App\Models\Tbl_code_transfer_logs;
use App\Models\Tbl_currency;
use App\Models\Tbl_dynamic_compression_record;
use App\Models\Tbl_earning_log;
use App\Models\Tbl_item;
use App\Models\Tbl_module_access;
use App\Models\Tbl_receipt;
use App\Models\Tbl_slot;
use App\Models\Tbl_top_recruiter;
use App\Models\Tbl_unilevel_points;
use App\Models\Tbl_wallet;
use App\Models\Tbl_wallet_log;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Validator;
use Crypt;
use Excel;
use Hash;

class AdminReportController extends AdminController
{
	public function load_month()
	{
		$data['date_from'] = Carbon::now()->startofMonth()->format('Y-m-d');
		$data['date_to'] = Carbon::now()->endofMonth()->format('Y-m-d');
		// dd($data);
		return Response()->json($data);
	}

	public function qoute_request()
	{
		$response = DB::table('tbl_qoute_request')
			->where('qoute_request_status', 0)
			->join('tbl_item', 'tbl_item.item_id', '=', 'tbl_qoute_request.qoute_request_item_id')
			->get();

		return Response()->json($response);
	}

	public function delete_qoute_request()
	{
		$response = DB::table('tbl_qoute_request')->where('qoute_request_id', Request::input('id'))->update(['qoute_request_status' => 1]);
	}

	public static function topRecruiter_list()
	{
		// $data = Request::input();
		// dd($data);
		$search = Request::input('search');
		$type = Request::input('type');
		$month = Request::input('month');
		if ($search == 'undefined') {
			$search = null;
		}
		if ($type == 'month') {
			if ($month == '' || $month == null || $month == 'undefined') {
				$date_from = Carbon::now()->startofMonth();
				$date_to = Carbon::now()->endofMonth();
			} else {
				$date_from = Carbon::parse($month)->startofMonth();
				$date_to = Carbon::parse($month)->endofMonth();
			}
		} else {
			if ($month == '' || $month == null || $month == 'undefined') {
				$date_from = Carbon::now()->startofYear();
				$date_to = Carbon::now()->endofYear();
			} else {
				$date_from = Carbon::parse($month)->startofYear();
				$date_to = Carbon::parse($month)->endofYear();
			}
		}

		$query = Tbl_slot::JoinTopRecruiter()
			->Owner()
			->select('tbl_top_recruiter.slot_id', DB::raw('sum(total_recruits) as total_recruits'), DB::raw('sum(total_leads) as total_leads'));

		if ($search != '' || $search != null) {
			$query->where('tbl_slot.slot_no', 'like', '%' . $search . '%')->orWhere('users.name', 'like', '%' . $search . '%');
		}
		if ($date_from != '' || $date_from != null) {
			$query->whereDate('tbl_top_recruiter.date_from', '>=', $date_from);
		}
		if ($date_to != '' || $date_to != null) {
			$query->whereDate('tbl_top_recruiter.date_to', '<=', $date_to);
		}

		$query = $query->groupBy('tbl_top_recruiter.slot_id');
		$query = $query->orderBy('total_recruits', 'DESC');
		$data = $query->paginate(15);

		$slot_ids = collect($data->items())->pluck('slot_id')->unique();
		$slots_details = Tbl_slot::whereIn('slot_id', $slot_ids)->Owner()->select('slot_id', 'slot_no', 'name', 'contact', 'email')->get()->keyBy('slot_id');

		foreach ($data as $key => $value) {
			$data[$key]->details = $slots_details->get($value->slot_id);
		}

		return Response()->json($data);
	}

	public static function cashflow_list()
	{
		$search = Request::input('search');
		$currency_id = Tbl_currency::where('currency_default', 1)->first()->currency_id;
		$query = Tbl_slot::where('tbl_slot.archive', 0)
			->Owner()
			->Wallet($currency_id);

		if ($search != '' || $search != null) {
			$query->where('tbl_slot.slot_no', 'like', '%' . $search . '%')->orWhere('users.name', 'like', '%' . $search . '%');
		}

		$data = $query->paginate(15);
		// dd($data);
		return Response()->json($data);
	}

	public static function bonussummary_list()
	{
		$search = Request::input('search');

		$query = Tbl_slot::where('tbl_slot.archive', 0)
			->Owner();

		if ($search != '' || $search != null) {
			$query->where('tbl_slot.slot_no', 'like', '%' . $search . '%')->orWhere('users.name', 'like', '%' . $search . '%');
		}

		$data = $query->paginate(15);
		// dd($data);
		return Response()->json($data);
	}

	public static function load_full_list()
	{
		$data['slot_list'] = Tbl_slot::where('archive', 0)
			->select('slot_id', 'slot_owner', 'slot_sponsor', 'slot_sponsor_member', 'slot_date_created')
			->paginate(15);

		$data['slot_count'] = Tbl_slot::where('archive', 0)
			->count();

		return Response()->json($data);
	}

	public static function recomputeTopRecruite()
	{
		$slot = Request::input('slot');
		// dd($slot);
		$date_from = Carbon::parse($slot['slot_date_created'])->startofMonth()->format('Y-m-d');
		$date_to = Carbon::parse($slot['slot_date_created'])->endofMonth()->format('Y-m-d');

		if ($slot['slot_sponsor'] != 0) {
			$check = Tbl_top_recruiter::where('slot_id', $slot['slot_sponsor'])->whereDate('date_from', '=', $date_from)->whereDate('date_to', '=', $date_to)->first();
			if (!$check) {
				$insert['slot_id'] = $slot['slot_sponsor'];
				$insert['date_from'] = $date_from;
				$insert['date_to'] = $date_to;

				Tbl_top_recruiter::insert($insert);
			}
			$recruit = Tbl_top_recruiter::where('slot_id', $slot['slot_sponsor'])->whereDate('date_from', '=', $date_from)->whereDate('date_to', '=', $date_to)->first();
			$update1['total_recruits'] = $recruit->total_recruits + 1;

			Tbl_top_recruiter::where('slot_id', $recruit->slot_id)->whereDate('date_from', '=', $date_from)->whereDate('date_to', '=', $date_to)->update($update1);

			if ($slot['slot_sponsor_member'] != 0) {
				$recruit2 = Tbl_top_recruiter::where('slot_id', $slot['slot_sponsor_member'])->whereDate('date_from', '=', $date_from)->whereDate('date_to', '=', $date_to)->first();

				$update2['total_leads'] = $recruit2->total_leads + 1;

				Tbl_top_recruiter::where('slot_id', $recruit2->slot_id)->whereDate('date_from', '=', $date_from)->whereDate('date_to', '=', $date_to)->update($update2);
			}
		}

		$response['response'] = 'success';

		return response()->json($response);
	}

	public static function get_branch()
	{
		$return = Tbl_branch::where('archived', 0)->get();

		return response()->json($return);
	}

	public static function get_survey_items()
	{
		$questions = DB::table('tbl_survey_question')->where('survey_archived', 0)->get();
		$question_ids = $questions->pluck('id');

		$choices = DB::table('tbl_survey_choices')
			->whereIn('survey_question_id', $question_ids)
			->where('survey_choices_status', 0)
			->get()
			->groupBy('survey_question_id');

		$choice_ids = $choices->flatten()->pluck('id');
		$answers_counts = DB::table('tbl_survey_answer')
			->whereIn('survey_choices_id', $choice_ids)
			->groupBy('survey_choices_id')
			->select('survey_choices_id', DB::raw('count(*) as count'))
			->get()
			->pluck('count', 'survey_choices_id');

		foreach ($questions as $key => $question) {
			$total_count = 0;
			$q_choices = $choices->get($question->id, collect());

			foreach ($q_choices as $choice) {
				$choice->count = $answers_counts->get($choice->id, 0);
				$total_count += $choice->count;
			}

			$questions[$key]->choices = $q_choices;
			$questions[$key]->total_count = $total_count;
		}

		return response()->json($questions);
	}

	public static function load_sales_report($export = 0)
	{
		$request = Request::input();
		$branch_id = (int) ($request['branch_id'] ?? 0);

		// dd($branch_id);
		// $item           = Request::input('item');
		if ($branch_id > 0) {
			$query = Tbl_receipt::where('tbl_receipt.retailer', $branch_id)->join('tbl_orders', 'tbl_orders.order_id', '=', 'tbl_receipt.receipt_order_id');
		} else {
			$query = Tbl_receipt::join('tbl_orders', 'tbl_orders.order_id', '=', 'tbl_receipt.receipt_order_id');
		}

		$query = $query->join('tbl_slot', 'tbl_slot.slot_id', '=', 'tbl_orders.buyer_slot_id');
		$query = $query->join('users', 'users.id', '=', 'tbl_slot.slot_owner');

		if (isset($request['sales_person'])) {
			if ($request['sales_person'] > 0) {
				$query = $query->where('tbl_orders.cashier_id', $request['sales_person']);
			}
		}

		if (isset($request['date_from'])) {
			$query->whereDate('receipt_date_created', '>=', $request['date_from']);
		}

		if (isset($request['date_to'])) {
			$query->whereDate('receipt_date_created', '<=', $request['date_to']);
		}

		$receipts = $query->paginate(15);

		$receipt_ids = collect($receipts->items())->pluck('receipt_id');
		$payment_method_ids = collect($receipts->items())->pluck('payment_method')->unique()->filter();

		$rel_items = DB::table('tbl_receipt_rel_item')
			->whereIn('rel_receipt_id', $receipt_ids)
			->join('tbl_item', 'tbl_item.item_id', '=', 'tbl_receipt_rel_item.item_id')
			->get()
			->groupBy('rel_receipt_id');

		$payment_methods = DB::table('tbl_cashier_payment_method')
			->whereIn('cashier_payment_method_id', $payment_method_ids)
			->get()
			->keyBy('cashier_payment_method_id');

		foreach ($receipts as $key => $value) {
			$receipts[$key]['items'] = $rel_items->get($value->receipt_id, collect());
			$receipts[$key]['payment_method'] = $payment_methods->get($value->payment_method);
		}

		return $receipts;
	}

	public static function load_eload_report()
	{
		$search = Request::input('search');
		$date_from = Request::input('date_from');
		$date_to = Request::input('date_to');
		$currency_id = Tbl_currency::where('currency_name', 'Load Wallet')->value('currency_id');
		if ($currency_id) {
			$query = Tbl_wallet_log::where('currency_id', $currency_id)
				->Eload()
				->leftJoin('tbl_slot', 'tbl_slot.slot_id', '=', 'tbl_wallet_log.wallet_log_slot_id')
				->leftJoin('users', 'users.id', '=', 'tbl_slot.slot_owner')
				->select('name', 'email', 'slot_no', 'wallet_log_amount', 'wallet_log_date_created')
				->orderBy('wallet_log_date_created', 'ASC');
			if ($search != '' || $search != null) {
				$query = $query->where('users.name', 'like', '%' . $search . '%')->orwhere('users.email', 'like', '%' . $search . '%');
			}
			if ($date_from != '' || $date_from != null) {
				$query->whereDate('wallet_log_date_created', '>=', $date_from);
			}
			if ($date_to != '' || $date_to != null) {
				$query->whereDate('wallet_log_date_created', '<=', $date_to);
			}

			$return = $query->paginate(15);
		} else {
			$return = null;
		}

		return $return;
	}

	public function adjust_wallet()
	{
		$search = Request::input('search');
		if ($search == 'undefined') {
			$search = null;
		}
		$date_from = Request::input('date_from');
		$date_to = Request::input('date_to');
		$query = Tbl_adjust_wallet_log::Slot()->Owner()->where('slot_status', 'active');
		if ($search != '' || $search != null) {
			// $query->where("tbl_slot.slot_no", "like", "%". $search . "%");
			$query->where('users.name', 'like', '%' . $search . '%');
			// ->orWhere("users.name", "like", "%". $search . "%")->orWhere("users.email", "like", "%". $search . "%");
		}
		if ($date_from != '' || $date_from != null) {
			$query->whereDate('date_created', '>=', $date_from);
		}
		if ($date_to != '' || $date_to != null) {
			$query->whereDate('date_created', '<=', $date_to);
		}
		$query->orderBy('date_created', 'Desc');
		// dd($query->get());
		$response = $query->paginate(15);

		return response()->json($response);
	}

	// Created By: Centy - 10-27-2023
	public function achievers_report()
	{
		$search = Request::input('search');
		$date_from = Request::input('date_from');
		$date_to = Request::input('date_to');

		// Check if search is 'undefined' and set it to null
		if ($search == 'undefined') {
			$search = null;
		}
		// Create a base query with the Tbl_achievers_rank_list model
		$query = Tbl_achievers_rank_list::Slot()->Owner()->AchieversRankAttribute();

		// Apply search filters
		if (!empty($search)) {
			$query
				->where('name', 'like', '%' . $search . '%')
				->orWhere('slot_no', 'like', '%' . $search . '%');
		}

		// Apply date_from filter
		if (!empty($date_from)) {
			$query->whereDate('qualified_date', '>=', $date_from);
		}

		// Apply date_to filter
		if (!empty($date_to)) {
			$query->whereDate('qualified_date', '<=', $date_to);
		}
		$query->orderBy('slot_no', 'asc');

		$response = $query->paginate(15);

		return response()->json($response);
	}

	public function achievers_report_full_list()
	{
		// Create a base query with the Tbl_achievers_rank_list model
		$query = Tbl_achievers_rank_list::Slot()->Owner()->AchieversRankAttribute();
		$response = Tbl_earning_log::where('earning_log_plan_type', 'ACHIEVERS RANK')
			->sum('earning_log_amount');
		return $response;
	}

	public function code_transfer()
	{
		$search = Request::input('search');
		if ($search == 'undefined') {
			$search = null;
		}

		$date_from = Request::input('date_from');
		$date_to = Request::input('date_to');
		if ($date_from == null || $date_to == null || $date_from == 'undefined' || $date_to == 'undefined') {
			$date_from = Carbon::now()->format('Y-m-d');
			$date_to = Carbon::now()->format('Y-m-d');
		}

		$query = Tbl_code_transfer_logs::leftJoin('tbl_codes', 'tbl_codes.code_id', '=', 'tbl_code_transfer_logs.code_id')
			->select('tbl_code_transfer_logs.code_id', 'from_slot', 'to_slot', 'original_slot', 'date_transfer', 'code_activation', 'code_pin');

		if ($search != '' || $search != null) {
			$search2 = Tbl_slot::where('slot_no', $search)->first() ? Tbl_slot::where('slot_no', $search)->first()->slot_id : null;
			// dd($search2);
			if ($search2 != '' || $search2 != null) {
				$query->where('from_slot', 'like', '%' . $search2 . '%')->orwhere('to_slot', 'like', '%' . $search2 . '%')->orwhere('original_slot', 'like', '%' . $search2 . '%');
			} else {
				$query->where('tbl_codes.code_activation', 'like', '%' . $search . '%');
			}
		}
		if ($date_from != '' || $date_from != null) {
			$query->whereDate('date_transfer', '>=', $date_from);
		}
		if ($date_to != '' || $date_to != null) {
			$query->whereDate('date_transfer', '<=', $date_to);
		}
		$query->orderBy('date_transfer', 'Desc');

		$response = $query->paginate(15);

		$slot_ids = collect($response->items())->pluck('from_slot')->concat(collect($response->items())->pluck('to_slot'))->concat(collect($response->items())->pluck('original_slot'))->unique()->filter();
		$slots_map = Tbl_slot::whereIn('slot_id', $slot_ids)->pluck('slot_no', 'slot_id');

		foreach ($response as $key => $value) {
			$response[$key]['from_slot_code'] = ['slot_no' => $slots_map->get($value->from_slot)];
			$response[$key]['to_slot_code'] = ['slot_no' => $slots_map->get($value->to_slot)];
			$response[$key]['original_slot_code'] = ['slot_no' => $slots_map->get($value->original_slot)];
		}
		return response()->json($response);
	}

	public function members_detail()
	{
		$search = Request::input('search');
		if ($search == 'undefined') {
			$search = null;
		}

		$date_from = Request::input('date_from');
		$date_to = Request::input('date_to');

		$query = User::leftJoin('tbl_country', 'tbl_country.country_id', '=', 'users.country_id');

		if ($search != '' || $search != null) {
			$query->where('name', 'like', '%' . $search . '%')->orWhere('users.email', 'like', '%' . $search . '%');
		}
		if ($date_from != '' || $date_from != null) {
			$query->whereDate('created_at', '>=', $date_from);
		}
		if ($date_to != '' || $date_to != null) {
			$query->whereDate('created_at', '<=', $date_to);
		}
		$query->orderBy('created_at', 'Asc');

		$response = $query->paginate(15);
		$user_ids = collect($response->items())->pluck('id');

		$addresses = Tbl_address::whereIn('user_id', $user_ids)
			->where('archived', 0)
			->where('is_default', 1)
			->Address()
			->get()
			->keyBy('user_id');

		$slots = Tbl_slot::whereIn('slot_owner', $user_ids)
			->select('slot_id', 'slot_no', 'slot_sponsor', 'slot_owner')
			->get();

		$sponsor_ids = $slots->pluck('slot_sponsor')->unique()->filter();
		$sponsors_map = Tbl_slot::whereIn('slot_id', $sponsor_ids)
			->Owner()
			->select('tbl_slot.slot_id', 'users.name', 'tbl_slot.slot_no')
			->get()
			->keyBy('slot_id');

		$slots_by_owner = $slots->groupBy('slot_owner');

		foreach ($response as $key => $value) {
			$addr = $addresses->get($value->id);
			$response[$key]->address_info = $addr->address_info ?? null;
			$response[$key]->barangay_city = $addr->brgyDesc ?? null;
			$response[$key]->city = $addr->citymunDesc ?? null;
			$response[$key]->region_province = $addr->provDesc ?? null;

			$user_slots = $slots_by_owner->get($value->id, collect());
			foreach ($user_slots as $slot) {
				$sponsor = $sponsors_map->get($slot->slot_sponsor);
				$slot->slot_sponsor_name = $sponsor->name ?? null;
				$slot->slot_sponsor_code = $sponsor->slot_no ?? null;
			}
			$response[$key]->slot_sponsor_no = $user_slots;
		}
		return response()->json($response);
	}

	public function unilevel_dynamic()
	{
		$search = Request::input('search');
		if ($search == 'undefined') {
			$search = null;
		}
		if (Request::input('date_month') == null || Request::input('date_month') == 'undefined' || Request::input('date_month') == '') {
			$start = Carbon::now()->startofMonth();
			$end = Carbon::now()->endofMonth();
		} else {
			$start = Carbon::parse(Request::input('date_month'))->startofMonth();
			$end = Carbon::parse(Request::input('date_month'))->endofMonth();
		}

		$query = Tbl_dynamic_compression_record::groupBy('tbl_dynamic_compression_record.slot_id')
			->leftJoin('tbl_slot', 'tbl_slot.slot_id', '=', 'tbl_dynamic_compression_record.slot_id')
			->leftJoin('users', 'users.id', '=', 'tbl_slot.slot_owner');
		if ($search != '' || $search != null) {
			$query->where('users.name', 'like', '%' . $search . '%')->orWhere('users.email', 'like', '%' . $search . '%')->orWhere('tbl_slot.slot_no', 'like', '%' . $search . '%');
		}
		if ($start != '' || $start != null) {
			$query->whereDate('tbl_dynamic_compression_record.date_created', '>=', $start);
		}
		if ($end != '' || $end != null) {
			$query->whereDate('tbl_dynamic_compression_record.date_created', '<=', $end);
		}
		$query->selectRaw('tbl_dynamic_compression_record.slot_id, sum(earned_points) as sum');
		$response = $query->paginate(15);
		$slot_ids = collect($response->items())->pluck('slot_id');

		$slots_details = Tbl_slot::whereIn('slot_id', $slot_ids)
			->Owner()
			->select('tbl_slot.slot_id', 'slot_no', 'email', 'name', 'contact', 'first_name', 'last_name', 'middle_name')
			->get()
			->keyBy('slot_id');

		foreach ($response as $key => $data) {
			$response[$key]->details = $slots_details->get($data->slot_id);
		}

		return response()->json($response);
	}

	public function top_seller_report()
	{
		$filter = Request::input();
		$data = Tbl_receipt::groupBy('buyer_slot_id');
		if (isset($filter['date_from'])) {
			$data = $data->whereDate('receipt_date_created', '>=', $filter['date_from']);
		}

		if (isset($filter['date_to'])) {
			$data = $data->whereDate('receipt_date_created', '<=', $filter['date_to']);
		}

		if (isset($filter['search'])) {
			$data = $data->where('buyer_slot_code', $filter['search']);
		}

		$top_sellers = $data
			->select('buyer_slot_id', DB::raw('sum(grand_total) as total_sales'))
			->orderBy('total_sales', 'desc')
			->paginate(20);

		$buyer_slot_ids = collect($top_sellers->items())->pluck('buyer_slot_id');
		$user_info_map = Tbl_slot::Owner()->whereIn('tbl_slot.slot_id', $buyer_slot_ids)->get()->keyBy('slot_id');

		$receipts_query = Tbl_receipt::whereIn('buyer_slot_id', $buyer_slot_ids)
			->join('tbl_receipt_rel_item', 'tbl_receipt_rel_item.rel_receipt_id', '=', 'tbl_receipt.receipt_id')
			->join('tbl_item', 'tbl_item.item_id', '=', 'tbl_receipt_rel_item.item_id')
			->select(
				'tbl_receipt.buyer_slot_id',
				'tbl_item.item_id',
				'tbl_item.item_sku',
				DB::raw('MAX(tbl_receipt_rel_item.price) as price'),
				DB::raw('SUM(tbl_receipt_rel_item.quantity * tbl_receipt_rel_item.price) as subtotal'),
				DB::raw('SUM(tbl_receipt_rel_item.quantity) as quantity')
			)
			->groupBy('tbl_receipt.buyer_slot_id', 'tbl_item.item_id', 'tbl_item.item_sku');

		if (isset($filter['item']) && $filter['item'] != 0) {
			$receipts_query->where('tbl_item.item_id', $filter['item']);
		}
		if (isset($filter['date_from'])) {
			$receipts_query->whereDate('tbl_receipt.receipt_date_created', '>=', $filter['date_from']);
		}
		if (isset($filter['date_to'])) {
			$receipts_query->whereDate('tbl_receipt.receipt_date_created', '<=', $filter['date_to']);
		}

		$receipts_grouped = $receipts_query->get()->groupBy('buyer_slot_id');

		foreach ($top_sellers as $key => $value) {
			$top_sellers[$key]['user_info'] = $user_info_map->get($value->buyer_slot_id);
			$top_sellers[$key]['receipts'] = $receipts_grouped->get($value->buyer_slot_id, collect());
		}

		return $top_sellers;
	}

	public function sales_receipt()
	{
		$receipt_id = Request::input('id');

		$return = Tbl_receipt::where('receipt_id', $receipt_id)->join('tbl_receipt_rel_item', 'tbl_receipt_rel_item.rel_receipt_id', '=', 'tbl_receipt.receipt_id')->join('tbl_item', 'tbl_item.item_id', '=', 'tbl_receipt_rel_item.item_id')->get();

		foreach ($return as $key => $value) {
			$discounted_price = json_decode($value->discount);
			$return[$key]['discounted_price'] = $discounted_price[0]->original_price - $discounted_price[0]->percentage;
		}

		return response()->json($return);
	}

	public function load_company_info()
	{
		$return = DB::table('tbl_company_details')->first();

		return response()->json($return);
	}

	public function promo_report()
	{
		// dd(Request::input());
		$date = Request::input('date_month') ? Request::input('date_month') : Carbon::now();
		$level = Request::input('level') ? Request::input('level') : 1;
		$item_id = Request::input('item') ? Request::input('item') : 0;
		$start = Carbon::parse($date)->startofMonth();
		$end = Carbon::parse($date)->endofMonth();

		$data = Tbl_slot::where('membership_inactive', 0)
			->where('slot_status', 'active')
			->Owner()
			->select('slot_id', 'slot_no', 'name', 'email', 'contact')
			->paginate(10);

		$slot_ids = collect($data->items())->pluck('slot_id');
		$all_slot_records = Tbl_dynamic_compression_record::whereIn('slot_id', $slot_ids)
			->where('dynamic_level', $level)
			->whereDate('start_date', '>=', $start)
			->whereDate('end_date', '<=', $end)
			->select('slot_id', 'cause_slot_id')
			->get()
			->groupBy('slot_id');

		$cause_slot_ids = $all_slot_records->flatten(1)->pluck('cause_slot_id')->unique();
		$points_query = Tbl_unilevel_points::whereIn('unilevel_points_slot_id', $cause_slot_ids)
			->where('unilevel_points_date_created', '>=', $start)
			->where('unilevel_points_date_created', '<=', $end);

		$item_sku = 'All';
		if ($item_id != 0) {
			$points_query->where('unilevel_item_id', $item_id);
			$item_sku = Tbl_item::where('tbl_item.archived', 0)->where('item_id', $item_id)->value('item_sku') ?? 'N/A';
		}

		$points_counts = $points_query
			->select('unilevel_points_slot_id', DB::raw('count(*) as count'))
			->groupBy('unilevel_points_slot_id')
			->get()
			->pluck('count', 'unilevel_points_slot_id');

		foreach ($data as $key => $data_raw) {
			$data[$key]['level'] = $level;
			$data[$key]['item'] = $item_sku;
			$slot_records = $all_slot_records->get($data_raw['slot_id'], collect());
			$data[$key]['slot_records'] = $slot_records;

			$item_counts = 0;
			foreach ($slot_records as $record) {
				$item_counts += $points_counts->get($record->cause_slot_id, 0);
			}
			$data[$key]['item_counts'] = $item_counts;
		}
		return $data;
	}

	public function get_items()
	{
		return Tbl_item::where('tbl_item.archived', 0)->get();
	}
}
