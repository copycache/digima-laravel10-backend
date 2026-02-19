<?php
namespace App\Http\Controllers\Admin;

use App\Globals\Audit_trail;
use App\Models\Tbl_video;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\DB;
class AdminVideoController extends AdminController
{
    public function get()
    {
        return $return = Tbl_video::orderby("video_sequence",'ASC')->orderby("video_is_archived",'ASC')->get();
    }
    public function add_video()
    {
        $params   =  Request::input();
        if(isset($params['video_title']) && $params['video_title'] != null && $params['video_title'] != '')
        {
            if(isset($params['video_url']) && $params['video_url'] != null && $params['video_url'] != '')
            {
                if(isset($params['video_sequence']) && $params['video_sequence'] != null && $params['video_sequence'] != '')
                {
                    if(($params['type'] ?? null) != 'upload')
                    {
                        if (preg_match('%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i', $params['video_url'], $match)) {
                            $video_url_id = $match[1];
                        }
                    }
                    $insert['video_title'] = $params['video_title'];
                    $insert['video_desc']  = isset($params['video_desc']) ? $params['video_desc'] : null;
                    $insert['video_url'] = $params['video_url'];
                    $insert['video_sequence'] = $params['video_sequence'];
                    $insert['type'] = $params['type'];
                    $insert['video_url_id'] = $video_url_id;

                    Tbl_video::insert($insert);

                    $return['status']  =  'Success';
                    $return['status_message'] = 'Video Added';    
                }
                else 
                {
                    $return['status']  =  'Warning';
                    $return['status_message'] = 'No Video Sequence';    
                }
            }
            else 
            {
                $return['status']  =  'Warning';
                $return['status_message'] = 'Please Select Video to Upload';    
            }
        }
        else 
        {
           $return['status']  =  'Warning';
           $return['status_message'] = 'No Video Title';    
        }
        return $return;
    }
    public function edit_video()
    {
        $params   =  Request::input();

        if(isset($params['video_title']) && $params['video_title'] != null && $params['video_title'] != '')
        {
            if(isset($params['video_url']) && $params['video_url'] != null && $params['video_url'] != '')
            {
                if(isset($params['video_sequence']) && $params['video_sequence'] != null && $params['video_sequence'] != '')
                {
                    if(($params['type'] ?? null) != 'upload')
                    {
                        if (preg_match('%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i', $params['video_url'], $match)) {
                            $video_url_id = $match[1];
                        }
                    }
                    $update['video_title'] = $params['video_title'];
                    $update['video_desc']  = isset($params['video_desc']) ? $params['video_desc'] : null;
                    $update['video_url'] = $params['video_url'];
                    $update['video_sequence'] = $params['video_sequence'];
                    $update['type'] = $params['type'];
                    $update['video_url_id'] = $video_url_id;

                    Tbl_video::where('video_id',$params['video_id'])->update($update);

                    $return['status']  =  'Success';
                    $return['status_message'] = 'Successfully Edited';    
                }
                else 
                {
                    $return['status']  =  'Warning';
                    $return['status_message'] = 'No Video Sequence';    
                }
            }
            else 
            {
                $return['status']  =  'Warning';
                $return['status_message'] = 'Please Select Video to Upload';    
            }
        }
        else 
        {
           $return['status']  =  'Warning';
           $return['status_message'] = 'No Video Title';    
        }
        return $return;
    }
    public function video_archived()
    {
        $params['video_id']   =  Request::input();
        if(isset($params['video_id']) && $params['video_id'])
        {
            Tbl_video::where('video_id',$params['video_id'])->update(['video_is_archived' => 1]);

            $return['status']  =  'Success';
            $return['status_message'] = 'Video Archived';  
        }
        else 
        {
            $return['status']  = 'Error';
            $return['status_message'] = 'Opps Something went wrong';    
        }

        return $return;
    }

    public function video_unarchived()
    {
        $params['video_id']   =  Request::input();
        if(isset($params['video_id']) && $params['video_id'])
        {
            Tbl_video::where('video_id',$params['video_id'])->update(['video_is_archived' => 0]);
            $return['status']  =  'Success';
            $return['status_message'] = 'Video Restored';  
        }
        else 
        {
            $return['status']  = 'Error';
            $return['status_message'] = 'Opps Something went wrong';    
        }

        return $return;
    }
    
}
