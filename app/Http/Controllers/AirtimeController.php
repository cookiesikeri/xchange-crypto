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
use App\Mail\AirtimeVendMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;
use App\Traits\SendSms;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AirtimeController extends Controller
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


    public function GetAirtime(Request $request)
    {

        $user = Auth::user();


        $ref = '51' . substr(uniqid(mt_rand(), true), 0, 8);

            $username = env('VTU_DOT_NG_USERNAME');
            $password = env('VTU_DOT_NG_PASSWORD');


            $data = array(
                'phone'       => $request->phone,
                'email'       => $request->email,
                'amount'      => $request->amount,
                'network_id'  => $request->network_id,
                'transaction_pin' => $request->transaction_pin,
                'transaction_id' => $this->utility->generateTransactionID(1),
            );

            $validator = Validator::make($data, [
                'phone'       => 'required|digits:11',
                'email'       => 'required|email',
                'amount'      => 'required|numeric|gt:0',
                'network_id'  => 'required|string',     //Rule::in(['MTN', 'Airtel', 'Glo', '9mobile']),
                'transaction_pin' => 'required'
            ]);

            if ($validator->fails()) {
                return response()->json($validator->errors());
            }

            $username = $username;
            $password = $password;
            $phone =  $request->phone;
            $network_id = $request->network_id;
            $amount = $request->amount;
            $transaction_id = $this->utility->generateTransactionID(1);
            $transaction_pin = $request->transaction_pin;
            $email = $request->email;

            $data['transaction_id'] = $this->utility->generateTransactionID(1);
            $data['status']         = 0;
            $data['commission']     = 0;
            $data['payment_method'] = 'WALLET';
            $data['platform']       = 'MOBILE';
            $data['user_id']        = $request->gLocatorID;
            $data['payment_ref']       = 'TXC_' . $ref;


            $userID = $this->utility->getUserByID(Auth::id());

            if (!empty($userID) && $userID !== -1) {
                // this user already has an account with us.
                $data['user_id'] = $userID;
            } else {
                return response()->json(['message'=>'Unauthenticated user.'], 401);
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

                WalletTransaction::on('mysql::write')->create([
                    'wallet_id'=>$user->wallet->id,
                    'type'=>'Debit',
                    'amount'=>$airtimePurchase->amount,
                    'description'=>'Airtime Purchase',
                    'bank_name'=>'TaheerXchange',
                    'transfer'=>false,
                    'transaction_type'=>'wallet',
                    'transaction_ref'=>'TXC_' . $ref,
                    'status'=>'success',
                ]);
                $airtimePurchase->update(['amount_paid' => $airtimePurchase->amount]);
            }else{
                return response()->json([
                    "message" => "Insufficient funds. Please TopUp your wallet",
                    'data' => $airtimePurchase->transaction_id,
                    'status' => 'false',
                ], 413);
            }

                $response = Http::withHeaders([
                    'Content-Type' => "application/json"
                ])->get(env('VTU_DOT_NG_BASE_URL')."airtime?username=$username&password=$password&phone=$phone&network_id=$network_id&amount=$amount");

                $this->saveUserActivity(ActivityType::AIRTIME, '', $user->id);

                // Mail::to($request->email)->send(new \App\Mail\AirtimeVendMail($airtimePurchase));

            return response()->json([
                "message" => "Airtime successfully delivered",
                'data' => $response['data'],
                'status' => 'success',
            ], 200);
        }

    }

