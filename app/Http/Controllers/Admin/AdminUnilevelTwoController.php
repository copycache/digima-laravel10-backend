<?php
namespace App\Http\Controllers\Admin;

use App\Globals\Audit_trail;
use App\Globals\Log;
use App\Globals\Slot;
use App\Globals\Special_plan;
use App\Globals\Wallet;
use App\Models\Tbl_dynamic_compression_record;
use App\Models\Tbl_membership;
use App\Models\Tbl_membership_unilevel_level;
use App\Models\Tbl_mlm_unilevel_settings;
use App\Models\Tbl_override_points;
use App\Models\Tbl_slot;
use App\Models\Tbl_stairstep_distribute;
use App\Models\Tbl_stairstep_distribute_full;
use App\Models\Tbl_stairstep_points;
use App\Models\Tbl_stairstep_rank;
use App\Models\Tbl_stairstep_settings;
use App\Models\Tbl_tree_sponsor;
use App\Models\Tbl_unilevel_distribute;
use App\Models\Tbl_unilevel_distribute_full;
use App\Models\Tbl_unilevel_points;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AdminUnilevelTwoController extends AdminController
{
	public static $gpv = 0;
	public static $child_level = null;
	public static $child_counter = null;

	public function distribute_points($slot_id, $start_date, $end_date, $full_id, $stairstep_full_id)
	{
		$settings = Tbl_mlm_unilevel_settings::first();
		$st_settings = Tbl_stairstep_settings::first();

		if ($settings && $st_settings) {
			$slot = Tbl_slot::where('slot_id', $slot_id)->first();
			$personal_as_group = $settings->personal_as_group;
			$gpv_to_wallet_conversion = $settings->gpv_to_wallet_conversion;
			$membership = Tbl_membership::where('membership_id', $slot->slot_membership)->first();

			if ($membership) {
				$start_date = Carbon::parse($start_date);
				$end_date = Carbon::parse($end_date);

				$total_sgpv = Tbl_stairstep_points::where('stairstep_points_slot_id', $slot->slot_id)->where('stairstep_points_type', 'STAIRSTEP_GPV')->where('stairstep_points_date_created', '>=', $start_date)->where('stairstep_points_date_created', '<=', $end_date)->sum('stairstep_points_amount');
				$total_spv = Tbl_stairstep_points::where('stairstep_points_slot_id', $slot->slot_id)->where('stairstep_points_type', 'STAIRSTEP_PPV')->where('stairstep_points_date_created', '>=', $start_date)->where('stairstep_points_date_created', '<=', $end_date)->sum('stairstep_points_amount');

				$required_pv = $membership->membership_required_pv;
				$required_spv = 0;
				$total_pv = Tbl_unilevel_points::where('unilevel_points_slot_id', $slot->slot_id)->where('unilevel_points_type', 'UNILEVEL_PPV')->where('unilevel_points_date_created', '>=', $start_date)->where('unilevel_points_date_created', '<=', $end_date)->where('unilevel_points_distribute', 0)->sum('unilevel_points_amount');
				// $total_gpv            = Tbl_unilevel_points::where("unilevel_points_slot_id",$slot->slot_id)->where("unilevel_points_type","UNILEVEL_GPV")->where("unilevel_points_date_created",">=",$start_date)->where("unilevel_points_date_created","<=",$end_date)->where("unilevel_points_distribute",0)->sum("unilevel_points_amount");
				$this->loop($slot_id, $slot_id, $start_date, $end_date);
				$total_gpv = Self::$gpv;

				$total_override = Tbl_override_points::where('slot_id', $slot->slot_id)->where('override_points_date_created', '>=', $start_date)->where('override_points_date_created', '<=', $end_date)->where('distributed', 0)->sum('override_amount');
				$convert_wallet = 0;
				$status = 0;
				$status_stairstep = 0;
				$unilevel_multiplier = 0;
				$override_converted = 0;
				$stairstep_override_points = $total_override;

				if ($personal_as_group == 1) {
					$total_gpv = $total_gpv + Tbl_unilevel_points::where('unilevel_points_slot_id', $slot->slot_id)->where('unilevel_points_type', 'UNILEVEL_PPV')->where('unilevel_points_date_created', '>=', $start_date)->where('unilevel_points_date_created', '<=', $end_date)->where('unilevel_points_distribute', 0)->sum('unilevel_points_amount');
				}

				$update_log['unilevel_points_distribute'] = 1;
				Tbl_unilevel_points::where('unilevel_points_slot_id', $slot->slot_id)->where('unilevel_points_type', 'UNILEVEL_PPV')->where('unilevel_points_date_created', '>=', $start_date)->where('unilevel_points_date_created', '<=', $end_date)->update($update_log);
				Tbl_unilevel_points::where('unilevel_points_slot_id', $slot->slot_id)->where('unilevel_points_type', 'UNILEVEL_GPV')->where('unilevel_points_date_created', '>=', $start_date)->where('unilevel_points_date_created', '<=', $end_date)->update($update_log);

				if ($total_pv >= $required_pv) {
					$status = 1;
					if ($total_gpv != 0) {
						$get_current_rank = Tbl_stairstep_rank::where('stairstep_rank_id', $slot->slot_stairstep_rank)->first();
						// if($get_current_rank)
						// {
						// if($get_current_rank->stairstep_commission != 0)
						// {
						$income_wallet = $total_gpv * $settings->gpv_to_wallet_conversion;
						if ($income_wallet != 0) {
							$convert_wallet = $income_wallet;

							$cd_package = Tbl_membership::where('hierarchy', 1)->pluck('membership_id')->first();
							$cd_slot_info = Tbl_slot::where('slot_id', $slot_id)->first();
							if ($cd_slot_info->slot_membership == $cd_package) {
								$cd_earnings = $income_wallet * 0.2;
								$income_wallet1 = $income_wallet - $cd_earnings;

								Log::insert_wallet($slot_id, $income_wallet1, 'UNILEVEL_COMMISSION');
								Log::insert_wallet($slot_id, $cd_earnings, 'UNILEVEL_COMMISSION', 18);
							} else {
								Log::insert_wallet($slot_id, $income_wallet, 'UNILEVEL_COMMISSION');
							}

							Log::insert_earnings($slot_id, $income_wallet, 'UNILEVEL_COMMISSION', 'UNILEVEL DISTRIBUTION', $slot_id, '', 0);
							Special_plan::infinity_bonus($slot, 'UNILEVEL', $income_wallet);
						}
						// }
						// }

						if ($total_pv >= $required_pv) {
							$status = 1;
							if ($total_gpv != 0) {
								$income_wallet = $total_gpv * $settings->gpv_to_wallet_conversion;
								if ($income_wallet != 0) {
									$convert_wallet = $income_wallet;

									$cd_package = Tbl_membership::where('hierarchy', 1)->pluck('membership_id')->first();
									$cd_slot_info = Tbl_slot::where('slot_id', $slot_id)->first();
									if ($cd_slot_info->slot_membership == $cd_package) {
										$cd_earnings = $income_wallet * 0.2;
										$income_wallet1 = $income_wallet - $cd_earnings;

										Log::insert_wallet($slot_id, $income_wallet1, 'UNILEVEL_COMMISSION');
										Log::insert_wallet($slot_id, $cd_earnings, 'UNILEVEL_COMMISSION', 18);
									} else {
										Log::insert_wallet($slot_id, $income_wallet, 'UNILEVEL_COMMISSION');
									}

									Log::insert_earnings($slot_id, $income_wallet, 'UNILEVEL_COMMISSION', 'UNILEVEL DISTRIBUTION', $slot_id, '', 0);
									Special_plan::infinity_bonus($slot, 'UNILEVEL', $income_wallet);
								}

								$stairstep_rank_id = $slot->slot_stairstep_rank;
								$get_root_current_rank = Tbl_stairstep_rank::where('stairstep_rank_id', $stairstep_rank_id)->first();

								$max_level = 0;
								$max_level_breakaway = 0;

								if ($get_root_current_rank) {
									$max_level = $get_root_current_rank->check_match_level ?? 0;
									$max_level_breakaway = $get_root_current_rank->breakaway_level ?? 0;
								}

								// Optimize parent distribution with bulk fetch
								$match_parent_ids = Tbl_tree_sponsor::where('sponsor_child_id', $slot_id)->where('sponsor_level', '<=', $max_level)->pluck('sponsor_parent_id');
								$breakaway_parent_ids = Tbl_tree_sponsor::where('sponsor_child_id', $slot_id)->where('sponsor_level', '<=', $max_level_breakaway)->pluck('sponsor_parent_id');
								$all_parent_ids = $match_parent_ids->concat($breakaway_parent_ids)->unique()->filter();

								if ($all_parent_ids->isNotEmpty()) {
									$parents_info = Tbl_slot::whereIn('slot_id', $all_parent_ids)
										->join('tbl_membership', 'tbl_membership.membership_id', '=', 'tbl_slot.slot_membership')
										->leftJoin('tbl_stairstep_rank', 'tbl_stairstep_rank.stairstep_rank_id', '=', 'tbl_slot.slot_stairstep_rank')
										->select('tbl_slot.*', 'tbl_membership.membership_required_pv', 'tbl_stairstep_rank.check_match_level as rank_match_level', 'tbl_stairstep_rank.check_match_percentage', 'tbl_stairstep_rank.breakaway_level as rank_breakaway_level', 'tbl_stairstep_rank.equal_bonus', 'tbl_stairstep_rank.stairstep_rank_id as current_rank_id')
										->get()
										->keyBy('slot_id');

									$parents_pv = Tbl_unilevel_points::whereIn('unilevel_points_slot_id', $all_parent_ids)
										->where('unilevel_points_type', 'UNILEVEL_PPV')
										->where('unilevel_points_date_created', '>=', $start_date)
										->where('unilevel_points_date_created', '<=', $end_date)
										->groupBy('unilevel_points_slot_id')
										->select('unilevel_points_slot_id', DB::raw('SUM(unilevel_points_amount) as total_pv'))
										->pluck('total_pv', 'unilevel_points_slot_id');

									$parents_levels = Tbl_tree_sponsor::where('sponsor_child_id', $slot_id)->whereIn('sponsor_parent_id', $all_parent_ids)->pluck('sponsor_level', 'sponsor_parent_id');

									foreach ($all_parent_ids as $pid) {
										$pt_info = $parents_info->get($pid);
										if (!$pt_info)
											continue;

										$pt_level = $parents_levels->get($pid);
										$pt_total_ppv = $parents_pv->get($pid, 0);

										// Check Match Income
										if ($pt_level <= $max_level && $pt_total_ppv >= $pt_info->membership_required_pv) {
											if ($pt_level <= $pt_info->rank_match_level && $pt_info->check_match_percentage != 0) {
												$check_match_income = $total_gpv * ($pt_info->check_match_percentage / 100);
												if ($check_match_income != 0) {
													Log::insert_wallet($pid, $check_match_income, 'CHECK_MATCH_INCOME');
													Log::insert_earnings($pid, $check_match_income, 'CHECK_MATCH_INCOME', 'UNILEVEL DISTRIBUTION', $slot_id, '', $pt_level);

													$distri_history = Tbl_unilevel_distribute::where('distribute_full_id', $full_id)->where('slot_id', $pid)->first();
													if ($distri_history) {
														$distri_history->increment('check_match_bonus', $check_match_income);
													}
												}
											}
										}

										// Breakaway Bonus
										if ($pt_level <= $max_level_breakaway && $get_root_current_rank) {
											if ($pt_level <= $pt_info->rank_breakaway_level && $pt_info->current_rank_id == $get_root_current_rank->stairstep_rank_id) {
												$breakaway_bonus = ($pt_info->equal_bonus / 100) * $total_gpv;
												if ($breakaway_bonus != 0) {
													Log::insert_wallet($pid, $breakaway_bonus, 'BREAKAWAY_BONUS');
													Log::insert_earnings($pid, $breakaway_bonus, 'BREAKAWAY_BONUS', 'UNILEVEL DISTRIBUTION', $slot_id, '', $pt_level);

													$distri_history = Tbl_unilevel_distribute::where('distribute_full_id', $full_id)->where('slot_id', $pid)->first();
													if ($distri_history) {
														$distri_history->increment('breakaway_bonus', $breakaway_bonus);
													}
												}
											}
										}
									}
								}
							}
						}
					}
				}

				if ($total_override != 0) {
					$check_stairstep_rank = Tbl_stairstep_rank::where('stairstep_rank_id', $slot->slot_stairstep_rank)->first();
					if ($check_stairstep_rank) {
						$required_spv = $check_stairstep_rank->stairstep_rank_personal;
						if ($total_spv >= $check_stairstep_rank->stairstep_rank_personal) {
							$status_stairstep = 1;
							$total_override_income = $total_override * $st_settings->override_multiplier;
							$override_converted = $total_override_income;
							Log::insert_wallet($slot_id, $total_override_income, 'OVERRIDE_COMMISSION');
							Log::insert_earnings($slot_id, $total_override_income, 'OVERRIDE_COMMISSION', 'UNILEVEL DISTRIBUTION', $slot_id, '', 0);
						}
					}
				}

				$update_override['distributed'] = 1;
				Tbl_override_points::where('slot_id', $slot->slot_id)->where('override_points_date_created', '>=', $start_date)->where('override_points_date_created', '<=', $end_date)->where('distributed', 0)->update($update_override);

				$insert_distribute['unilevel_distribute_date_start'] = $start_date;
				$insert_distribute['unilevel_distribute_end_start'] = $end_date;
				$insert_distribute['unilevel_personal_pv'] = $total_pv;
				$insert_distribute['unilevel_required_personal_pv'] = $required_pv;
				$insert_distribute['unilevel_group_pv'] = round($total_gpv, 2);
				$insert_distribute['status'] = $status;
				$insert_distribute['unilevel_amount'] = $convert_wallet;
				$insert_distribute['unilevel_multiplier'] = $unilevel_multiplier;
				$insert_distribute['unilevel_date_distributed'] = Carbon::now();
				$insert_distribute['distribute_full_id'] = $full_id;
				$insert_distribute['slot_id'] = $slot_id;

				Tbl_unilevel_distribute::insert($insert_distribute);

				$insert_stairstep_distribute['stairstep_distribute_date_start'] = $start_date;
				$insert_stairstep_distribute['stairstep_distribute_end_start'] = $end_date;
				$insert_stairstep_distribute['stairstep_personal_pv'] = $total_spv;
				$insert_stairstep_distribute['stairstep_required_personal_pv'] = $required_spv;
				$insert_stairstep_distribute['stairstep_group_pv'] = $total_sgpv;
				$insert_stairstep_distribute['status'] = $status_stairstep;
				$insert_stairstep_distribute['stairstep_override_amount'] = $override_converted;
				$insert_stairstep_distribute['stairstep_override_points'] = $stairstep_override_points;
				$insert_stairstep_distribute['stairstep_multiplier'] = 1;
				$insert_stairstep_distribute['stairstep_date_distributed'] = Carbon::now();
				$insert_stairstep_distribute['distribute_full_id'] = $stairstep_full_id;
				$insert_stairstep_distribute['slot_id'] = $slot_id;
				$insert_stairstep_distribute['current_rank_id'] = $slot->slot_stairstep_rank;

				Tbl_stairstep_distribute::insert($insert_stairstep_distribute);
			}
		}

		$return['status'] = 'success';
		$return['status_code'] = 201;
		$return['status_message'] = 'Slot Distributed';

		return $return;
	}

	public function distribute_slot()
	{
		$slot_id = Request::input('slot_id');
		$start_date = Request::input('start_date');
		$end_date = Request::input('end_date') . ' 23:59:59';
		$full_id = Request::input('full_id');
		$stairstep_full_id = Request::input('stairstep_full_id');

		Self::$child_level[$slot_id] = 0;
		Self::$child_counter[$slot_id] = 0;

		// $parent_id			= 1;
		// $slot_id			= 1;
		// $start_date			= "10/01/2018";
		// $end_date			= "10/31/2018";

		$response = $this->distribute_points($slot_id, $start_date, $end_date, $full_id, $stairstep_full_id);

		return response()->json($response, 200);
	}

	public function loop($parent_id, $root_id, $start_date, $end_date)
	{
		// Optimize by bulk loading all descendants and their data at the start
		$parent_slot = Tbl_slot::where('slot_id', $parent_id)->first();
		if (!$parent_slot)
			return;
		$parent_membership = Tbl_membership::where('membership_id', $parent_slot->slot_membership)->first();
		if (!$parent_membership)
			return;

		$settings = Tbl_mlm_unilevel_settings::first();
		if (!$settings)
			return;

		$all_descendants = Tbl_tree_sponsor::where('sponsor_parent_id', $root_id)->get();
		$descendant_ids = $all_descendants->pluck('sponsor_child_id')->unique();

		$slots_info = Tbl_slot::whereIn('slot_id', $descendant_ids)->get()->keyBy('slot_id');
		$membership_ids = $slots_info->pluck('slot_membership')->unique();
		$memberships = Tbl_membership::whereIn('membership_id', $membership_ids)->get()->keyBy('membership_id');

		$points = Tbl_unilevel_points::whereIn('unilevel_points_slot_id', $descendant_ids)
			->where('unilevel_points_type', 'UNILEVEL_PPV')
			->where('unilevel_points_date_created', '>=', $start_date)
			->where('unilevel_points_date_created', '<=', $end_date)
			->where('unilevel_points_distribute', 0)
			->groupBy('unilevel_points_slot_id')
			->select('unilevel_points_slot_id', DB::raw('SUM(unilevel_points_amount) as total_pv'))
			->get()
			->pluck('total_pv', 'unilevel_points_slot_id');

		$level_settings = Tbl_membership_unilevel_level::where('membership_id', $parent_membership->membership_id)
			->get()
			->groupBy('membership_level');

		$tree_map = $all_descendants->where('sponsor_level', 1)->groupBy('sponsor_parent_id');

		$this->internal_loop($parent_id, $root_id, $start_date, $end_date, $slots_info, $memberships, $points, $level_settings, $tree_map, $all_descendants);
	}

	protected function internal_loop($parent_id, $current_slot_id, $start_date, $end_date, $slots_info, $memberships, $points, $level_settings, $tree_map, $all_descendants)
	{
		$children = $tree_map->get($current_slot_id, collect());

		foreach ($children as $tree) {
			$child_id = $tree->sponsor_child_id;
			$slot = $slots_info->get($child_id);
			if (!$slot)
				continue;

			$membership = $memberships->get($slot->slot_membership);
			if (!$membership)
				continue;

			$next_level = Self::$child_counter[$current_slot_id] + 1;
			$check = $level_settings->get($next_level, collect())->where('membership_entry_id', $membership->membership_id)->first();

			if ($check) {
				$total_pv = $points->get($child_id, 0);
				$required_pv = $membership->membership_required_pv;

				$plus = 0;
				if ($total_pv >= $required_pv) {
					$cause_level = $all_descendants->where('sponsor_parent_id', $parent_id)->where('sponsor_child_id', $child_id)->first()->sponsor_level ?? 0;

					$earned_points = $total_pv * ($check->membership_percentage / 100);
					$dynamic_record = [
						'slot_id' => $parent_id,
						'earned_points' => $earned_points,
						'cause_slot_id' => $child_id,
						'dynamic_level' => $next_level,
						'cause_slot_level' => $cause_level,
						'start_date' => $start_date,
						'end_date' => $end_date,
						'date_created' => Carbon::now(),
						'cause_slot_ppv' => $total_pv,
						'cause_slot_percentage' => $check->membership_percentage,
					];
					Tbl_dynamic_compression_record::insert($dynamic_record);

					Self::$gpv += $earned_points;
					Self::$child_level[$child_id] = $next_level;
					$plus = 1;
				}

				Self::$child_counter[$child_id] = Self::$child_counter[$current_slot_id] + $plus;
				$this->internal_loop($parent_id, $child_id, $start_date, $end_date, $slots_info, $memberships, $points, $level_settings, $tree_map, $all_descendants);
			}
		}
	}

	public function distribute_start()
	{
		$user = Request::user()->id;
		$action = 'Unilevel Distribute';
		Audit_trail::audit(null, null, $user, $action);
		$start_date = Request::input('start_date');
		$end_date = Request::input('end_date') . ' 23:59:59';
		$insert['start_date'] = $start_date;
		$insert['end_date'] = $end_date;
		$insert['distribution_date'] = Carbon::now();

		$return['status'] = 'success';
		$return['status_code'] = 201;
		$return['status_message'] = 'Slot Distribution Start';
		$return['distribute_full_id'] = Tbl_unilevel_distribute_full::insertGetId($insert);
		$return['stairstep_distribute_full_id'] = Tbl_stairstep_distribute_full::insertGetId($insert);

		return response()->json($return, 200);
	}
}
