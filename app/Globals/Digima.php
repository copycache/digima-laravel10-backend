<?php
namespace App\Globals;
use stdClass;

class Digima
{
    public static function getConfig()
    {
        $config             = new stdClass();
        $config->dg_project = env('dg_project', false);
        $config->dg_key     = env('dg_key', false);
        $config->dg_host    = env('dg_host', false);
        return $config;    
    }
    public static function addRequest($number_of_request)
    {
        $config = Self::getConfig();

        if($config->dg_host)
        {
            $post["dg_project"]     = $config->dg_project;
            $post["dg_key"]         = $config->dg_key;
            $post["request_count"]  = $number_of_request;

            $ch                     = curl_init('http://' . $config->dg_host . "/api/add_request");

            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post);

            $response               = curl_exec($ch);
            $response_json          = json_decode($response);
            curl_close($ch);

            return $response_json;
        }
        else
        {
            return false;
        }
    }
    public static function updateStatistic($member_count, $slot_count, $total_pay_in, $total_pay_out)
    {
        $config = Self::getConfig();

        if($config->dg_host)
        {
            $post["dg_project"]     = $config->dg_project;
            $post["dg_key"]         = $config->dg_key;
            $post["member_count"]   = $member_count;
            $post["slot_count"]     = $slot_count;
            $post["total_pay_in"]   = $total_pay_in;
            $post["total_pay_out"]  = $total_pay_out;

            $ch                     = curl_init('http://' . $config->dg_host . "/api/statistic");

            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post);

            $response               = curl_exec($ch);
            $response_json          = json_decode($response);
            curl_close($ch);

            return $response_json;
        }
        else
        {
            return false;
        }
    }

    public static function accessControl($module = "admin")
    {
        $config = Self::getConfig();

        if($config->dg_host)
        {
            $post["dg_project"]     = $config->dg_project;
            $post["dg_key"]         = $config->dg_key;

            $ch                     = curl_init('http://' . $config->dg_host . "/api/check_status");

            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post);

            $response               = curl_exec($ch);
            curl_close($ch);

            if($response == "Invalid Key")
            {
                abort(403, 'Invalid Digima API Key');
            }
            elseif($response == "BLOCKED" && $module == "admin")
            {
                abort(403, 'Your account has been temporarily blocked.');
            }
            elseif($response == "DEACTIVATED")
            {
                abort(403, 'Your account has been deactivated.');
            }
        }
        else
        {
            return false;
        }
    }
}