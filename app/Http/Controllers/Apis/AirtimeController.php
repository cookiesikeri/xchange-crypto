<?php

namespace App\Http\Controllers\Apis;

use App\Models\Wallet;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Http\Controllers\Apis\UtilityController;
use App\Models\WalletTransaction;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use HenryEjemuta\LaravelVtuDotNG\Facades\VtuDotNG;
use HenryEjemuta\LaravelVtuDotNG\Classes\VtuDotNGResponse;



class AirtimeController extends Controller
{
    public $utility;

    public function __construct(UtilityController $utility)
    {
        $this->utility = $utility;
    }

    public function requesqwsdft(Request $request)
    {

        $data = array(
            'phone'       => $request->phone,
            'email'       => $request->email,
            'amount'      => $request->amount,
            'service_id'  => $request->service_id,
            'amount_paid' => $request->amount_paid,
            'transaction_pin' => $request->transaction_pin,
        );

        $validator = Validator::make($data, [
            'phone'       => 'required|digits:11',
            'email'       => 'required|email',
            'amount'      => 'required|numeric|gt:0',
            'service_id'  => 'required|numeric',     //Rule::in(['MTN', 'Airtel', 'Glo', '9mobile']),
            'transaction_pin' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors());
        }

        $data['transaction_id'] = $this->utility->generateTransactionID(1);
        $data['status']         = 0;
        $data['commission']     = 0;
        $data['payment_method'] = 'WALLET';
        $data['platform']       = 'MOBILE';
        $data['user_id']        = $request->gLocatorID;
        $data['payment_ref']       = 'NILL';

        $resp = array(
            'msg'   =>  'Pending...',
            'tNo'   =>  'starting',
            'success' => false
        );

        $userID = $this->utility->getUserByID(Auth::id());

        if (!empty($userID) && $userID !== -1) {
            // this user already has an account with us.
            $data['user_id'] = $userID;
        } else {
            return response()->json(['message'=>'Unauthenticated user.'], 401);
            // this user does not exist so create an account for the user;
        }
        $airtimePurchase = \App\Models\AirtimeTransaction::on('mysql::write')->create($data);

        $user = \App\Models\User::on('mysql::read')->where('email', $airtimePurchase->email)->first();
        if(!$user){
            return response()->json(['message'=>'User not found.'], 404);
        }
        if(empty($user->transaction_pin)){
            return response()->json(['message'=>'Transaction Pin not set.'], 422);
        }

        if(!Hash::check($data['transaction_pin'], $user->transaction_pin))
        {
            return response()->json(['message'=>'Incorrect Pin!'], 404);
        }
        $current_balance = floatval($user->wallet->balance);
        if($current_balance > floatval($airtimePurchase->amount)){
            $new_balance = floatval($current_balance) - floatval($airtimePurchase->amount);
            $wallet = Wallet::on('mysql::write')->where('user_id',$user->id)->first();
            $wallet->update(['balance' => $new_balance]);
            $serviceName = app('App\Http\Controllers\Apis\UtilityController')->resolveServiceNameFromID($airtimePurchase->service_id);
            $description = $serviceName . ' N ' . intval($airtimePurchase->amount) . ' to ' . $airtimePurchase->phone;
            WalletTransaction::on('mysql::write')->create([
                'wallet_id'=>$user->wallet->id,
                'type'=>'Debit',
                'amount'=>$airtimePurchase->amount,
                'description'=>'Airtime Purchase',
                'bank_name'=>'Transave',
                'transfer'=>false,
                'transaction_type'=>'wallet',
                'status'=>'success',
            ]);
            $airtimePurchase->update(['amount_paid' => $airtimePurchase->amount]);
            // fire event to dispense airtime
            event(new \App\Events\AirtimeRequestEvent($airtimePurchase));
            $resp['msg'] = 'You should receive your airtime shortly with a notification to your e-mail and phone number.';
            $resp['tNo'] = $airtimePurchase->transaction_id;
            $resp['success'] = true;
        }else{
            $resp['msg'] = 'Insufficient funds. Please TopUp your wallet.';
            $resp['tNo'] = $airtimePurchase->transaction_id;
        }

        // after committing the transaction to the database, now lets try to verify whether the payment was actually successful before vending.

            $verifyPayment = $this->utility->verifyPayment($data['payment_ref'], 'airtime', $airtimePurchase->id, env('MODE'));

        if ($verifyPayment == -1) {
            $resp['msg'] = 'We were unable to initiate the process of verifying your payment status. Please contact our customer support lines with your transaction reference for help.';
            $resp['tNo'] = $airtimePurchase->transaction_id;
        } else if ((1 <= $verifyPayment) && ($verifyPayment <= 88)) {
            $resp['msg'] = 'Unfortunately, our servers encountered an error trying to validate your payment status. Please contact our customer support lines with your transaction reference for help.';
            $resp['tNo'] = $airtimePurchase->transaction_id;
        } else if ($verifyPayment == 404) {
            $resp['msg'] = 'We could not find your payment transaction reference. Your payment might have been declined. Please contact our customer support lines with your transaction reference for help.';
            $resp['tNo'] = $airtimePurchase->transaction_id;
        } else if ($verifyPayment == '503') {
            $resp['msg'] = 'Unable to verify transaction. Please contact our customer support lines with your transaction reference for help.';
            $resp['tNo'] = $airtimePurchase->transaction_id;
        } else if ($verifyPayment == 100) {
            // fire event to dispense airtime
            event(new \App\Events\AirtimeRequestEvent($airtimePurchase));
            $resp['msg'] = 'You should receive your airtime shortly with a notification to your e-mail and phone number.';
            $resp['tNo'] = $airtimePurchase->transaction_id;
            $resp['success'] = true;
        } else {
            $resp['msg'] = $verifyPayment;
            $resp['tNo'] = $airtimePurchase->transaction_id;
        }
        return response()->json($resp);
    }


        public function request($phone, $amount, $network_id){

            $username = env('VTU_DOT_NG_USERNAME');
            $password = env('VTU_DOT_NG_PASSWORD');




                $username = $username;
                $password = $password;
                $phone =  $phone;
                $amount = $amount;
                $network_id = $network_id;

                $curl = curl_init();

            curl_setopt_array($curl, [
            CURLOPT_HTTPHEADER => [
                "Content-Type: application/json"
            ],
            // CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_URL => "https://vtu.ng/wp-json/api/v1/airtime/" . $username . "/" . $password . "/" . $phone . "/" . $network_id . "/" . $amount,
            $api_call = $base_url . "username=" . $username . "&password=" . $password . "&sender=" . $sender . "&mobiles=" . $phone . "&message=" . $message;

            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => "GET",
            ]);

            $response = curl_exec($curl);
            $error = curl_error($curl);

            curl_close($curl);

            if ($error) {
                return response()->json($error);
            } else {
                return response()->json([ 'status' => true, 'message' => 'airtime fetched successfully', 'response' => $response ], 200);
            }

    }

    public function sendSMS()
    {
        $curl = curl_init();
        $base_url = "https://vtu.ng/wp-json/api/v1/airtime/";
        $username = "Taheerexchange";
        $password = "Doris2108!";
        $network_id = "mtn";
        $amount = "50";
        $phone = "08132364213";

        // $api_call = $base_url . "username=" . $username . "&password=" . $password . "&network_id=" . $network_id . "&phone=" . $phone . "&amount=" . $amount;

        // curl_setopt_array($curl, array(
        //     CURLOPT_URL => $api_call,
        //     CURLOPT_RETURNTRANSFER => true,
        //     CURLOPT_ENCODING => "",
        //     // CURLOPT_MAXREDIRS => 10,
        //     // CURLOPT_TIMEOUT => 30,
        //     // CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        //     CURLOPT_CUSTOMREQUEST => "GET",
        //     CURLOPT_HTTPHEADER => array(
        //         "Content-Type: application/json"
        //     ),
        // ));

        $response = curl_exec($curl);

        $err = curl_error($curl);

        curl_close($curl);
    }


    public function request11($phone, $networkID, $amount){

        $username = env('VTU_DOT_NG_USERNAME');
        $password = env('VTU_DOT_NG_PASSWORD');

        $url = "https://vtu.ng/wp-json/api/v1/airtime";
        $fields = [
            'password'=>$password,
            'username'=>$username,

            'phone'=>$phone,
            'network_id'=>$networkID,
            'amount'=>$amount
        ];
        $fields_string = http_build_query($fields);
        //open connection
        $ch = curl_init();

        //set the url, number of POST vars, POST data
        curl_setopt($ch,CURLOPT_URL, $url);
        curl_setopt($ch,CURLOPT_POST, true);
        curl_setopt($ch,CURLOPT_POSTFIELDS, $fields_string);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "Content-Type: application/json"
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
}
