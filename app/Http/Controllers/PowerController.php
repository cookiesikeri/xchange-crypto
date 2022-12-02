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
            $request->validate([
                'pin'=>'required|string',
                'token'=>'required',
                'amount_paid'=>'required',
                'user_id'=>'required|uuid',
            ]);

            //$userID     = $request->input('user_id');
            $userID = Auth::id();
            $amountPaid = $request->input('amount_paid');
            $token      = $request->input('token');
            $pin = strval($request->input('pin'));


            $resp = array(
                'status'    =>  0,
                'msg'       =>  'Pending',
                'success'   => false
            );

            // get the last active transaction tied to the token and this users account.
            $powerTransaction = \App\Models\PowerTransaction::on('mysql::write')->where('access_token', $token)->where('status', 0)->first();
            if(!$powerTransaction) {
                // that's actually very strange because at this point, the user has paid and has a valid token but the query above could not locate the transaction.
                // apologise to the user. Don't take the blame but make them feel special anyways.
                $resp['status'] = -1000;
                $resp['msg'] = 'Power Transaction not found.';

                return response()->json($resp);
            }

            $rUserID = $this->utility->getUserByGLocatorID($userID, $powerTransaction->email);

            $user = \App\Models\User::on('mysql::read')->findOrFail($powerTransaction->user_id);
            if(!$user){
                return response()->json(['message'=>'User not found.'], 404);
            }
            if(empty($user->transaction_pin)){
                return response()->json(['message'=>'Transaction Pin not set.'], 403);
            }

            if(Hash::check($pin, $user->transaction_pin))
            {
                $serviceName = \App\Models\Service::on('mysql::read')->find($powerTransaction->service_id);
                $wallet = Wallet::on('mysql::write')->where('user_id', $user->id)->first();
                $acc = AccountNumber::on('mysql::read')->where([['wallet_id', $user->wallet->id], ['account_name', 'Wallet ID']])->first();
                $current_balance = intval($wallet->balance);
                if($current_balance > (intval($powerTransaction->amount) + intval($serviceName->service_charge))){

                    $description = $serviceName->name . ' N ' . $powerTransaction->amount_paid . ' to ' . $powerTransaction->meter_num;
                    WalletTransaction::on('mysql::write')->create([
                        'wallet_id'=>$user->wallet->id,
                        'type'=>'Debit',
                        'amount'=>$powerTransaction->amount,
                        'description'=>'Power bill payment',
                        'transfer'=>false,
                        'reference'=>$powerTransaction->transaction_id,
                        'status'=>'success',
                    ]);
                    $powerTransaction->update(['amount_paid' => ($powerTransaction->amount + $serviceName->service_charge)]);
                    $powerTransaction->update([
                        'status'        =>  1,
                        'amount_paid'   =>  $amountPaid
                    ]);
                    // oh yeah we got the transaction details. Simply parse the transaction details to the power vending event handler to dispense the token.
                    //event(new \App\Events\NewPowerVendEvent($powerTransaction, $amountPaid, $token));
                    $this->utility->processPowerTransaction($powerTransaction, $amountPaid, $token);


                    // as of now the event listener must have updated transaction status in the database so go get it.
                    $updatedTransaction = \App\Models\PowerTransaction::on('mysql::read')->find($powerTransaction->id);
                    if($updatedTransaction->status != 2) {
                        // something must have gone wrong while trying to dispense.
                        $resp['status'] = -2000;
                        $resp['msg'] = 'Failed.';
                    } else {
                        $new_balance = $current_balance - intval($powerTransaction->amount + $serviceName->service_charge);
                        $wallet->update(['balance' => $new_balance]);
                        $resp['status'] = 2000;
                        $resp['success'] = true;
                        $resp['msg'] = array('token' => $updatedTransaction->token, 'units' => $updatedTransaction->units, 'amountPaid' => $updatedTransaction->amount_paid, 'transID' => $updatedTransaction->transaction_id);
                    }
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
                    $resp['status'] = -1;
                    $resp['msg'] = 'Insufficient funds. Please TopUp your wallet.';
                }

                return response()->json($resp);
            }else{
                return response()->json(['message'=>'Incorrect Pin!'], 404);
            }
        }catch(ValidationException $e){
            return response()->json(['message'=>$e->getMessage(), 'errors'=>$e->errors()], 420);
        }
    }

}
