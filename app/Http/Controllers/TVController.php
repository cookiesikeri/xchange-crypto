<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Traits\ManagesUsers;
use Illuminate\Http\Request;
use App\Interfaces\UserInterface;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use App\Models\WalletTransfer;
use Carbon\Carbon;
use App\Traits\ManagesResponse;
use App\Enums\ActivityType;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use App\Http\Controllers\Apis\UtilityController;
use App\Models\AccountNumber;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class TVController extends Controller
{
    use  ManagesResponse, ManagesUsers;


    /**
     * UserController constructor.
     * @param UserInterface $user
     */
    public $utility;

    public function __construct(UtilityController $utility)
    {
        $this->utility = $utility;
    }

    public function getTVInfo($providerID) {
        $bundles = \App\Models\TVBundle::on('mysql::read')->where('service_id', $providerID)->get();

        if(count($bundles) <= 0) {
            return response()->json('404');
        }

        return response()->json($bundles);
    }

    public function getCardInfo(Request $request)
    {
        try{

            $validator = Validator::make($request->all(), [
                'service_id'    =>  'required',
                'customer_id' =>  'required|numeric'
            ]);

            if ($validator->fails()) {
                return response()->json($validator->errors(), 422);
            }

            $userID = $this->utility->getUserByID(Auth::id());
            if(!empty($userID) && $userID !== -1) {
                $data['user_id'] = $userID;
            } else {
                return response()->json(['message'=>'User not Authenticated!!', 'status'=>404], 404);

            }

            $username = env('VTU_DOT_NG_USERNAME');
            $password = env('VTU_DOT_NG_PASSWORD');

            $username = $username;
            $password = $password;
            $customer_id =  $request->customer_id;
            $service_id = $request->service_id;


            $response = Http::withHeaders([
                'Content-Type' => "application/json"
            ])->get(env('VTU_DOT_NG_BASE_URL')."verify-customer?username=$username&password=$password&customer_id=$customer_id&service_id=$service_id");

            return response()->json(['account'=>$response['data']]);

        }catch(Exception $e){
            return response()->json(['message'=>$e->getMessage()], 422);
        }

    }

    public function getCardInfo1(Request $request) {
        try{

            $username = env('VTU_DOT_NG_USERNAME');
            $password = env('VTU_DOT_NG_PASSWORD');

            $username = $username;
            $password = $password;
            $customer_id =  $request->customer_id;
            $service_id = $request->service_id;


            $validator = Validator::make($request->all(), [
                'service_id'    =>  'required',
                'customer_id' =>  'required|numeric'
            ]);

            if ($validator->fails()) {
                return response()->json($validator->errors(), 422);
            }

                // $userID = $this->utility->getUserByID(Auth::id());
                // if(!empty($userID) && $userID !== -1) {
                //     $data['user_id'] = $userID;
                // } else {
                //     return response()->json(['message'=>'User not Authenticated!!', 'status'=>404], 404);

                // }

                $response = Http::withHeaders([
                    'Content-Type' => "application/json"
                ])->get(env('VTU_DOT_NG_BASE_URL')."verify-customer?username=$username&password=$password&customer_id=$customer_id&service_id=$service_id");

                return response()->json([
                    "message" => "Customer details successfully retrieved",
                    'data' => $response,
                    'status' => 'success',
                ], 200);



        }catch(Exception $e){
            return response()->json(['message'=>$e->getMessage()], 420);
        }
    }




            public function request(Request $request) {
                $resp = array(
                    'status'    =>  0,
                    'msg'       =>  'Pending',
                    'success'   => false
                );
                try{
                    $request->validate([
                        'transaction_pin'=>'required',
                        'user_id'=>'required|uuid',
                        'token'=>'required',
                        'amount_paid'=>'required',
                    ]);
                }catch(ValidationException $e){
                    return response()->json(['message'=>$e->getMessage(), 'errors'=>$e->errors()], 420);
                }

                $locatorID          = Auth::id();
                $amountPaid         = $request->input('amount_paid');
                $token              = $request->input('token');
                $pin = $request->input('transaction_pin');


                $tvTransaction = \App\Models\TVTransaction::on('mysql::write')->where('access_token', $token)->first();

                if(!$tvTransaction) {
                    $resp['status'] = -1000;
                    $resp['msg'] = 'TV Transaction not found.';

                    return response()->json($resp);
                }

                $rUserID = $this->utility->getUserByGLocatorID($locatorID, $tvTransaction->email);

                $user = \App\Models\User::on('mysql::read')->find($tvTransaction->user_id);
                if(!$user){
                    return response()->json(['message'=>'User not found.'], 404);
                }
                if(empty($user->transaction_pin)){
                    return response()->json(['message'=>'Transaction Pin not set.'], 422);
                }

                if(!Hash::check($pin, $user->transaction_pin))
                {
                    return response()->json(['message'=>'Incorrect Pin!'], 404);
                }

                $tvProvider = \App\Models\Service::on('mysql::read')->find($tvTransaction->service_id);
                $tvBundle = \App\Models\TVBundle::on('mysql::read')->find($tvTransaction->tv_bundles_id);
                $service = \App\Models\Service::on('mysql::read')->find($tvTransaction->service_id);

                $wallet = Wallet::on('mysql::write')->where('user_id', $user->id)->first();
                $acc = AccountNumber::on('mysql::read')->where([['wallet_id', $user->wallet->id], ['account_name', 'Wallet ID']])->first();
                $current_balance = intval($wallet->balance);
                if($current_balance > (intval($tvTransaction->amount) + intval($service->service_charge))){

                    $description = $tvBundle->name . ' N ' . $tvTransaction->amount . ' to ' . $tvTransaction->smartcard_num;
                    WalletTransaction::on('mysql::write')->create([
                        'wallet_id'=>$user->wallet->id,
                        'type'=>'Debit',
                        'amount'=>$tvTransaction->amount,
                        'description'=>'TV Bill Payment',
                        'transfer'=>false,
                        'reference'=>$tvTransaction->transaction_id,
                        'status'=>'success',
                    ]);
                    $tvTransaction->update(['amount_paid' => ($tvBundle->amount + $service->service_charge)]);

                    $tvTransaction->update([
                        'status'            =>  1,
                        'amount_paid'       =>  $amountPaid,
                        'payment_method'    =>  'WALLET',
                    ]);

                    event(new \App\Events\NewTVVendEvent($tvTransaction));
                    $updatedTransaction = \App\Models\TVTransaction::on('mysql::read')->where('access_token', $token)->first();
                    if($updatedTransaction->status != 2) {
                        // something must have gone wrong while trying to dispense.
                        $resp['status'] = -2000;
                        $resp['msg'] = 'Failed.';
                    } else {
                        $new_balance = $current_balance - intval($tvBundle->amount + $service->service_charge);
                        $wallet->update(['balance' => $new_balance]);
                        $resp['status'] = 2000;
                        $resp['success'] = true;
                        $resp['msg'] = 'success';
                    }
                }else{
                    $resp['status'] = -1;
                    $resp['msg'] = 'Insufficient funds. Please TopUp your wallet.';
                    WalletTransaction::on('mysql::write')->create([
                        'wallet_id'=>$user->wallet->id,
                        'type'=>'Debit',
                        'amount'=>$tvTransaction->amount,
                        'description'=>'TV Bill Payment',
                        'transfer'=>false,
                        'reference'=>$tvTransaction->transaction_id,
                        'status'=>'failed',
                    ]);
                }


                return response()->json($resp);
            }


}
