<?php

namespace App\Http\Controllers;

use App\Models\SupportIssueTracker;
use App\Models\WalletTransaction;
use Illuminate\Support\Facades\Storage;

class Functions{

    public function __construct()
    {
    }

    public function test(){
        return 'Tis from Functions.';
    }

    public function verifyPaystackPayment($ref){
        //Log::info('lets try to verify payment to fund wallet on paystack');
        $amount = 0;
        $verified = 0;
        $result = array();
        $cardDetails = array();
        $key = env('PAYSTACK_SECRET_KEY');
        /*if($mode == 2) {
            $key = env('PAYSTACK_LIVE_PRIVATE_KEY');
        }*/
        $url = 'https://api.paystack.co/transaction/verify/' . $ref;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt(
            $ch, CURLOPT_HTTPHEADER, ['Authorization: Bearer ' . $key]
        );
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        $request = curl_exec($ch);
        if(curl_errno($ch)) {
            $verified = curl_errno($ch);
            //Log::info('cURL error occured while trying to verify payment.');
            //Log::error(curl_error($ch));

            // verification failed
            $verified = -1;
        }else {
            if ($request) {
                $result = json_decode($request, true);
                //return $result;
                //Log::info('result from paystack');
                //Log::info($result);
                if(isset($result["data"]) && $result["data"]["status"] == "success") {
                    // at this point, payment has been verified.
                    $verified = 100;
                    $amount = $result["data"]["amount"];
                    $cardDetails = $result["data"]["authorization"];
                } elseif(isset($result["data"]) && $result["data"]["status"] == "failed") {
                    // $resp['msg'] = 'Transaction not found!';
                    //Transaction failed
                    $verified = 400;
                }elseif(!isset($result['data'])){
                    $verified = 404;
                }
            } else {
                // $resp['msg'] = 'Unable to verify transaction!';
                $verified = 503;
            }
        }

        curl_close($ch);
        return array('status' => $verified, 'amount' => $amount,  'card'=>$cardDetails);
    }


    public function createTransferRecipient($name, $accNum, $bankCode){
        $url = "https://api.paystack.co/transferrecipient";
        $fields = [
            'type'=>'nuban',
            'name'=>$name,
            'account_number'=>$accNum,
            'bank_code'=>$bankCode,
            'currency'=>'NGN'
        ];
        $fields_string = http_build_query($fields);
        //open connection
        $ch = curl_init();

        //set the url, number of POST vars, POST data
        curl_setopt($ch,CURLOPT_URL, $url);
        curl_setopt($ch,CURLOPT_POST, true);
        curl_setopt($ch,CURLOPT_POSTFIELDS, $fields_string);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "Authorization: Bearer ". env('PAYSTACK_SECRET_KEY'),
        ));

        //So that curl_exec returns the contents of the cURL; rather than echoing it
        curl_setopt($ch,CURLOPT_RETURNTRANSFER, true);

        //execute post
        $result = curl_exec($ch);

        if(curl_errno($ch)){
            return array('message'=>curl_error($ch), 'error'=>true);
        }

        $res = json_decode($result, true);

        curl_close($ch);
        return array('error'=>false, 'data'=>$res['data']);
    }

    public function paystackTransfer($amount, $recipient, $reason){
        //return 'in pst';
        $url = "https://api.paystack.co/transfer";
        $fields = [
            'source'=>'balance',
            'amount'=>$amount,
            'recipient'=>$recipient,
            'reason'=>$reason,
        ];
        $fields_string = http_build_query($fields);
        //open connection
        $ch = curl_init();

        //set the url, number of POST vars, POST data
        curl_setopt($ch,CURLOPT_URL, $url);
        curl_setopt($ch,CURLOPT_POST, true);
        curl_setopt($ch,CURLOPT_POSTFIELDS, $fields_string);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "Authorization: Bearer ". env('PAYSTACK_SECRET_KEY'),
        ));

        //So that curl_exec returns the contents of the cURL; rather than echoing it
        curl_setopt($ch,CURLOPT_RETURNTRANSFER, true);

        //execute post
        $result = curl_exec($ch);

        if(curl_errno($ch)){
            return array('message'=>curl_error($ch), 'error'=>true);
        }

        $res = json_decode($result, true);

        curl_close($ch);
        return array('error'=>false, 'data'=>$res);
    }

    public function insertIssueTracker($user_id, $support_id, $tracker)
    {
        $track = SupportIssueTracker::create([
            'user_id'=>$user_id,
            'support_id'=>$support_id,
            'tracker'=>$tracker,
        ]);

        return $track;
    }

}
