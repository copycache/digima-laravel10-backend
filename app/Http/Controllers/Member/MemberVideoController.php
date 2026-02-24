<?php
namespace App\Http\Controllers\Member;

use App\Globals\Log;
use App\Globals\Slot;
use App\Globals\User_process;
use App\Models\Tbl_earning_log;
use App\Models\Tbl_slot;
use App\Models\Tbl_video;
use App\Models\Tbl_watch_earn_settings;
use App\Models\Tbl_watched_videos;
use Carbon\Carbon;
use Illuminate\Support\Facades\Request;

class MemberVideoController extends MemberController
{
    public function get_video()
    {
        $slot_id = Request::input('slot_id') ? Request::input('slot_id') : null;
        $owner_id = Request::user()->id;
        if ($slot_id) {
            $slot_id_check = Tbl_slot::where('slot_id', $slot_id)->where('membership_inactive', 0)->where('slot_status', 'active')->where('slot_owner', $owner_id)->first();
            if ($slot_id_check) {
                $settings = Tbl_watch_earn_settings::first() ? Tbl_watch_earn_settings::first() : null;
                if ($settings) {
                    $today = Carbon::now()->format('Y-m-d');
                    $today_video_count = Tbl_earning_log::where('earning_log_slot_id', $slot_id)->where('earning_log_plan_type', 'WATCH AND EARN')->whereDate('earning_log_date_created', $today)->count();
                    $sum_earning = Tbl_earning_log::where('earning_log_slot_id', $slot_id)->where('earning_log_plan_type', 'WATCH AND EARN')->sum('earning_log_amount');
                    // dd(($today_video_count < $settings->watch_earn_video_max));
                    if ($today_video_count < $settings->watch_earn_video_max) {
                        if ($sum_earning < $settings->watch_earn_maximum_amount) {
                            $videos = Tbl_video::where('video_is_archived', 0)->orderby('video_sequence', 'ASC')->get();
                            $watched_video_ids = Tbl_watched_videos::where('watched_slot_id', $slot_id)->pluck('watched_video_id')->toArray();

                            $found = false;
                            foreach ($videos as $key => $video) {
                                if (!in_array($video->video_id, $watched_video_ids)) {
                                    $return['video'] = $video;
                                    $found = true;
                                    break;
                                }
                            }

                            if (!$found) {
                                $return['status'] = 'Success';
                                $return['status_message'] = 'No video today try again some other time.';
                            }
                        } else {
                            $return['status'] = 'Success';
                            $return['status_message'] = 'No video today try again some other time.';
                        }
                    } else {
                        $return['status'] = 'Success';
                        $return['status_message'] = 'No video today try again some other time.';
                    }
                } else {
                    $return['status'] = 'Error';
                    $return['status_message'] = 'Oops something went wrong!!!';
                }
            } else {
                $return['status'] = 'Error';
                $return['status_message'] = 'Oops something went wrong!!';
            }
        } else {
            $return['status'] = 'Error';
            $return['status_message'] = 'Oops something went wrong!';
        }

        return $return;
    }

    public function video_reward()
    {
        $slot_id = Request::input('slot_id') ? Request::input('slot_id') : null;
        $video_id = Request::input('video_id') ? Request::input('video_id') : null;
        $owner_id = Request::user()->id;
        $dup_check = User_process::check($owner_id);
        if ($dup_check != 0) {
            $return['status'] = 'Error';
            $return['status_message'] = 'Oops something went wrong!';
        } else {
            if ($slot_id) {
                if ($video_id) {
                    $slot_id_check = Tbl_slot::where('slot_id', $slot_id)->where('membership_inactive', 0)->where('slot_status', 'active')->where('slot_owner', $owner_id)->first();
                    if ($slot_id_check) {
                        $video_watched = Tbl_watched_videos::where('watched_slot_id', $slot_id)->where('watched_video_id', $video_id)->first();
                        if (!$video_watched) {
                            $settings = Tbl_watch_earn_settings::first() ? Tbl_watch_earn_settings::first() : null;
                            if ($settings) {
                                $today = Carbon::now()->format('Y-m-d');
                                $today_video_count = Tbl_earning_log::where('earning_log_slot_id', $slot_id)->where('earning_log_plan_type', 'WATCH AND EARN')->whereDate('earning_log_date_created', $today)->count();
                                $sum_earning = Tbl_earning_log::where('earning_log_slot_id', $slot_id)->where('earning_log_plan_type', 'WATCH AND EARN')->sum('earning_log_amount');
                                if ($today_video_count < $settings->watch_earn_video_max) {
                                    if ($sum_earning < $settings->watch_earn_maximum_amount) {
                                        $insert['watched_slot_id'] = $slot_id;
                                        $insert['watched_video_id'] = $video_id;
                                        $insert['watch_video_date_created'] = Carbon::now();

                                        Tbl_watched_videos::insert($insert);
                                        $total_earning = $sum_earning + $settings->watch_earn_video_amount;
                                        if ($total_earning <= $settings->watch_earn_maximum_amount) {
                                            $amount = $settings->watch_earn_video_amount;
                                        } else {
                                            $diff = $total_earning - $settings->watch_earn_maximum_amount;
                                            $amount = $settings->watch_earn_video_amount - $diff;
                                        }
                                        $details = '';
                                        Log::insert_wallet($slot_id, $amount, 'WATCH AND EARN');
                                        Log::insert_earnings($slot_id, $amount, 'WATCH AND EARN', 'WATCH VIDEO', $slot_id, $details, 0);

                                        $return['status'] = 'Success';
                                        $return['status_message'] = 'Successfully Receive the money';
                                    } else {
                                        $return['status'] = 'Warning';
                                        $return['status_message'] = 'You reach the limit.';
                                    }
                                } else {
                                    $return['status'] = 'Warning';
                                    $return['status_message'] = 'You reach the limit.';
                                }
                            } else {
                                $return['status'] = 'Error';
                                $return['status_message'] = 'Oops something went wrong!!!!!';
                            }
                        } else {
                            $return['status'] = 'Error';
                            $return['status_message'] = 'Oops something went wrong!!!!';
                        }
                    } else {
                        $return['status'] = 'Error';
                        $return['status_message'] = 'Oops something went wrong!!!';
                    }
                } else {
                    $return['status'] = 'Error';
                    $return['status_message'] = 'Oops something went wrong!!';
                }
            } else {
                $return['status'] = 'Error';
                $return['status_message'] = 'Oops something went wrong!';
            }
        }
        return $return;
    }

    public function get_settings()
    {
        $return = Tbl_watch_earn_settings::first() ? Tbl_watch_earn_settings::first()->watch_earn_minimum_sec : 0;
        return $return;
    }

    public function get_recent()
    {
        $response = Tbl_watched_videos::where('watched_slot_id', Request::input('slot_id'))->leftjoin('tbl_video', 'tbl_video.video_id', 'tbl_watched_videos.watched_video_id')->paginate(4);
        return $response;
    }

    public function play_recent()
    {
        $response = tbl_video::where('video_id', Request::input('watched_video_id'))->get();
        return $response;
    }
}
