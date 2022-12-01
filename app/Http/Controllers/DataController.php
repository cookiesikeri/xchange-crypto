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
use App\Mail\DataVendMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class DataController extends Controller
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

    public function GetData(Request $request)
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
            'variation_id' => $request->variation_id,
            'transaction_id' => $this->utility->generateTransactionID(2),
        );

        $validator = Validator::make($data, [
            'phone'       => 'required|digits:11',
            'email'       => 'required|email',
            'amount'      => 'required|numeric|gt:0',
            'network_id'  => 'required|string',     //Rule::in(['MTN', 'Airtel', 'Glo', '9mobile']),
            'transaction_pin' => 'required',
            'variation_id' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors());
        }

        $username = $username;
        $password = $password;
        $phone =  $request->phone;
        $network_id = $request->network_id;
        $variation_id = $request->variation_id;
        $amount = $request->amount;
        $transaction_id = $this->utility->generateTransactionID(2);
        $transaction_pin = $request->transaction_pin;
        $email = $request->email;

        $data['transaction_id'] = $this->utility->generateTransactionID(2);
        $data['status']         = 0;
        $data['commission']     = 0;
        $data['payment_method'] = 'WALLET';
        $data['platform']       = 'MOBILE';
        $data['user_id']        = $request->gLocatorID;
        $data['payment_ref']       = 'TXC_' . $ref;




            $userID = $this->utility->getUserByID(Auth::id());
            if(!empty($userID) && $userID !== -1) {
                $data['user_id'] = $userID;
            } else {
                return response()->json(['message'=>'Unauthenticated user.'], 422);

            }
            $dataPurchase = \App\Models\DataTransaction::on('mysql::write')->create($data);

            $user = \App\Models\User::on('mysql::read')->where('email', $dataPurchase->email)->first();

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
            if($current_balance > floatval($dataPurchase->amount)){
                $new_balance = $current_balance - intval($dataPurchase->amount);
                $wallet = Wallet::on('mysql::write')->where('user_id',$user->id)->first();
                $wallet->update(['balance' => $new_balance]);

                // $serviceName = app('App\Http\Controllers\Apis\UtilityController')->resolveServiceNameFromID($dataPurchase->service_id);
                // $description = $serviceName . ' N ' . $dataPurchase->amount . ' to ' . $dataPurchase->phone;

                WalletTransaction::on('mysql::write')->create([
                    'wallet_id'=>$user->wallet->id,
                    'type'=>'Debit',
                    'amount'=>$dataPurchase->amount,
                    'status'=>'success',
                    'description'=>'Data purchase',
                ]);
                $dataPurchase->update(['amount_paid' => $dataPurchase->amount]);

                $response = Http::withHeaders([
                    'Content-Type' => "application/json"
                ])->get(env('VTU_DOT_NG_BASE_URL')."data?username=$username&password=$password&phone=$phone&network_id=$network_id&amount=$amount&variation_id=$variation_id");

                $this->saveUserActivity(ActivityType::DATA, '', $user->id);

            return response()->json([
                "message" => "You should receive your data bundle shortly with a notification to your e-mail and phone number",
                'data' => $response['data'],
                'status' => 'success',
            ], 200);
            }else{
                WalletTransaction::on('mysql::write')->create([
                    'wallet_id'=>$user->wallet->id,
                    'type'=>'Debit',
                    'amount'=>$dataPurchase->amount,
                    'status'=>'failed',
                    'description'=>'Data purchase',
                ]);
                $resp['msg'] = 'Insufficient funds. Please TopUp your wallet.';
                $resp['tNo'] = $dataPurchase->transaction_id;
            }

        }




}
