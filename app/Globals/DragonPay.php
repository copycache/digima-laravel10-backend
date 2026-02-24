<?php
namespace App\Globals;

use DB;
use Carbon\Carbon;
use Request;
use Redirect;
use App\Models\Tbl_dragonpay_transaction;
use App\Models\Tbl_dragonpay_settings;

/* Merchant ID        : LABELLEMOI
   Merchant Password  : hXrmR4DSv6VShr9
   Mode               : Test

  Production Pass     : 83gw7y8TGNwwGNo
   Dragonpay Code by  : Mark Kenneth Reyes ^_^
*/ 

class DragonPay
{
    public static function create_transaction($transaction_id = 0, $buyer_id, $product_summary = null, $subtotal = 0, $email = null, $txnid = null)
    {
        $get_dragonpay_settings                         = Tbl_dragonpay_settings::first();

        if(isset($get_dragonpay_settings))
        {
          $merchant_id                                  = $get_dragonpay_settings->merchant_id;
          $merchant_password                            = $get_dragonpay_settings->merchant_password;
          $mode                                         = $get_dragonpay_settings->mode;
          $errors                                       = array();     

          $parameters = array(
            'merchantid'                                => $merchant_id,
            'txnid'                                     => $txnid,
            'amount'                                    => $subtotal,
            'ccy'                                       => 'PHP',
            'description'                               => $product_summary,
            'email'                                     => $email,
          );

          $fields = array(
            'txnid' => array(
                'label'                                 => 'Transaction ID',
                'type'                                  => 'text',
                'attributes'                            => array(),
                'filter'                                => FILTER_SANITIZE_STRING,
                'filter_flags'                          => array(FILTER_FLAG_STRIP_LOW),
            ),
            'amount' => array(
                'label'                                 => 'Amount',
                'type'                                  => 'number',
                'attributes'                            => array('step="0.01"'),
                'filter'                                => FILTER_SANITIZE_NUMBER_FLOAT,
                'filter_flags'                          => array(FILTER_FLAG_ALLOW_THOUSAND, FILTER_FLAG_ALLOW_FRACTION),
            ),
            'description' => array(
                'label'                                 => 'Description',
                'type'                                  => 'text',
                'attributes'                            => array(),
                'filter'                                => FILTER_SANITIZE_STRING,
                'filter_flags'                          => array(FILTER_FLAG_STRIP_LOW),
            ),
            'email' => array(
                'label'                                 => 'Email',
                'type'                                  => 'email',
                'attributes'                            => array(),
                'filter'                                => FILTER_SANITIZE_EMAIL,
                'filter_flags'                          => array(),
            ),
          );

          foreach ($fields as $key => $value) 
          {
            if (isset($_POST[$key])) 
            {
                $parameters[$key]                       = filter_input(INPUT_POST, $key, $value['filter'],
                                                          array_reduce($value['filter_flags'], function ($a, $b) { return $a | $b; }, 0));
            }
          }
          if (!is_numeric($parameters['amount'])) {
            $errors[]                                   = 'Amount should be a number.';
          }
          else if ($parameters['amount'] <= 0) {
            $errors[]                                   = 'Amount should be greater than 0.';
          }

          if (empty($errors)) {
            $parameters['amount']                       = number_format($parameters['amount'], 2, '.', '');
            $parameters['key']                          = $merchant_password;
            $digest_string                              = implode(':', $parameters);
            unset($parameters['key']);  

            $parameters['digest']                       = sha1($digest_string);
            $parameters['param1']                       = $transaction_id;
          
            
            if ($mode == 'test') 
            {
              $url                                      = 'http://test.dragonpay.ph/Pay.aspx?';
            }
            elseif ($mode == 'live') {
              $url                                      = 'https://gw.dragonpay.ph/Pay.aspx?';
            }

            $url                                        .= http_build_query($parameters, '', '&');

            $response['url']                            = $url;

            return $response;
          }   
        }
        else {
          dd("Error, Please Contact Administrator");
        }    
    }
}
