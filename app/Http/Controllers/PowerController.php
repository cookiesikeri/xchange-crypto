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
use App\Models\Service;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;


class PowerController extends Controller
{

    use  ManagesResponse, ManagesUsers;


    /**
     * UserController constructor.
     * @param UserInterface $user
     */
    public $utility;
    protected $jwt;

    public function __construct(UtilityController $utility, JWTAuth $jwt)
    {
        $this->utility = $utility;
        $this->jwt = $jwt;
    }

    public function getMeterInfo(Request $request)
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
            $variation_id = "prepaid";
            $customer_id =  $request->customer_id;
            $service_id = $request->service_id;

            $response = Http::withHeaders([
                'Content-Type' => "application/json"
            ])->get(env('VTU_DOT_NG_BASE_URL')."verify-customer?username=$username&password=$password&customer_id=$customer_id&service_id=$service_id&variation_id=$variation_id");

            return response()->json(['account'=>$response['data']], 200);

        }catch(Exception $e){
            return response()->json(['message'=>$e->getMessage()], 422);
        }

    }

    public function BuyPower(Request $request) {

        try{

            $user = Auth::user();

            $ref = '51' . substr(uniqid(mt_rand(), true), 0, 8);

            $data = array(
                'phone'       => $user->phone,
                'email'       => $user->email,
                'amount'      => $request->amount,
                'meter_num'  => $request->meter_number,
                'service_id'  => $request->service_id,
                'transaction_pin'  => $request->transaction_pin,
                'variation_id' => $request->variation_id,
                'transaction_id' => $this->utility->generateTransactionID(3),
            );

            $request->validate([
                'variation_id'=>'required|string',
                'amount'=>'required|max:200000',
                'user_id'=>'required|uuid',
                'transaction_pin'=>'required|numeric',
                'meter_number'=>'required|numeric'
            ]);

            if ($request->amount <= 500) {

                return response()->json(['message'=>'Amount to transfer CANNOT be less than 600'], 422);
            }

            $userID = Auth::id();
            $amount         = $request->input('amount');
            $pin = strval($request->input('transaction_pin'));

            $username = env('VTU_DOT_NG_USERNAME');
            $password = env('VTU_DOT_NG_PASSWORD');

            $username = $username;
            $password = $password;
            $service_id = $request->service_id;
            $variation_id = $request->variation_id;
            $meter_number = $request->meter_number;

            $data['transaction_id'] = $this->utility->generateTransactionID(3);
            $data['status']         = 0;
            $data['commission']     = 0;
            $data['payment_method'] = 'WALLET';
            $data['platform']       = 'MOBILE';
            $data['user_id']        = auth()->user()->id;
            $data['payment_ref']       = 'TXC_' . $ref;

            // get the last active transaction tied to the token and this users account.
            $powerTransaction = \App\Models\PowerTransaction::on('mysql::write')->create($data);

            $userID = $this->utility->getUserByID(Auth::id());
            if(!empty($userID) && $userID !== -1) {
                $data['user_id'] = $userID;
            } else {
                return response()->json(['message'=>'Unauthenticated user.'], 422);

            }
            $user = User::on('mysql::read')->findOrFail($powerTransaction->user_id);
            if(!$user){
                return response()->json(['message'=>'User not found.'], 404);
            }
            if(empty($user->transaction_pin)){
                return response()->json(['message'=>'Transaction Pin not set.'], 403);
            }

            if(!Hash::check($data['transaction_pin'], $user->transaction_pin))
            {
                return response()->json(['message'=>'Incorrect Pin!'], 404);
            }


                $wallet = Wallet::on('mysql::write')->where('user_id', $user->id)->first();
                $acc = AccountNumber::on('mysql::read')->where([['wallet_id', $user->wallet->id], ['account_name', 'Wallet ID']])->first();

                $current_balance = floatval($user->wallet->balance);
            if($current_balance > floatval($powerTransaction->amount)){
                    $new_balance = $current_balance - intval($powerTransaction->amount);
                    $wallet = Wallet::on('mysql::write')->where('user_id',$user->id)->first();
                    $wallet->update(['balance' => $new_balance]);

                    $powerTransaction->update(['amount' => $powerTransaction->amount]);

                    $powerTransaction->update([
                        'status'            =>  1,
                        'amount'       =>  $amount,
                        'payment_method'    =>  'WALLET',
                    ]);

                    $powerTransaction->update(['amount' => $powerTransaction->amount]);

                    $response = Http::withHeaders([
                        'Content-Type' => "application/json"
                    ])->get(env('VTU_DOT_NG_BASE_URL')."electricity?username=$username&password=$password&phone=$user->phone&meter_number=$meter_number&service_id=$service_id&variation_id=$variation_id&amount=$amount");


                    $new_balance = $current_balance - intval($powerTransaction->amount);
                    $wallet->update(['balance' => $new_balance]);
                    $resp['status'] = 2000;
                    $resp['success'] = true;
                    $resp['msg'] = 'success';


                    $this->saveUserActivity(ActivityType::ELECTRICITY, '', $user->id);

                    WalletTransaction::on('mysql::write')->create([
                        'wallet_id'=>$user->wallet->id,
                        'type'=>'Debit',
                        'amount'=>$powerTransaction->amount,
                        'description'=>'TV Bill Payment',
                        'transfer'=>false,
                        'transaction_ref'=>'TXC_' . $ref,
                        'reference'=>$powerTransaction->transaction_id,
                        'status'=>'success',
                    ]);

                }else{
                    WalletTransaction::on('mysql::write')->create([
                        'wallet_id'=>$user->wallet->id,
                        'type'=>'Debit',
                        'amount'=>$powerTransaction->amount,
                        'description'=>'Power bill payment',
                        'transfer'=>false,
                        'reference'=>$powerTransaction->transaction_id,
                        'status'=>'failed',
                    ]);
                    return response()->json([
                        "message" => "Insufficient funds. Please TopUp your wallet.",
                        'status' => false,
                    ], 413);
                }

                return response()->json([
                    "message" => "Electricity bill successfully paid",
                    'data' => $response['data'],
                    'status' => 'success',
                ], 200);



        }catch(ValidationException $e){
            return response()->json(['message'=>$e->getMessage(), 'errors'=>$e->errors()], 420);
        }

    }





}
