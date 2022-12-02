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
use App\Models\TVBundle;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;

class TVController extends Controller
{
    use  ManagesResponse, ManagesUsers;


    /**
     * UserController constructor.
     * @param UserInterface $user
     */
    protected $jwt;
    public $utility;

    public function __construct(UtilityController $utility, JWTAuth $jwt)
    {
        $this->utility = $utility;
        $this->jwt = $jwt;
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

            return response()->json(['account'=>$response['data']], 200);

        }catch(Exception $e){
            return response()->json(['message'=>$e->getMessage()], 422);
        }

    }

    public function GetTVplan(Request $request) {

        $user = Auth::user();

        $ref = '51' . substr(uniqid(mt_rand(), true), 0, 8);
        $data = array(
            'phone'       => $user->phone,
            'email'       => $user->email,
            'amount'      => $request->amount,
            'smartcard_number'  => $request->smartcard_number,
            'service_id'  => $request->service_id,
            'transaction_pin'  => $request->transaction_pin,
            'variation_id' => $request->variation_id,
            'transaction_id' => $this->utility->generateTransactionID(4),
        );


        try{
            $validator = Validator::make($data, [
                'transaction_pin'=>'required|numeric',
                'user_id'=>'required|uuid',
                'smartcard_number'=>'required|numeric',
                'service_id'    =>  'required',
                'variation_id' =>  'required'
            ]);


        $amountPaid         = $request->input('amount');
        $pin = $request->input('transaction_pin');

        $username = env('VTU_DOT_NG_USERNAME');
        $password = env('VTU_DOT_NG_PASSWORD');

        $username = $username;
        $password = $password;
        $service_id = $request->service_id;
        $smartcard_number =  $request->smartcard_number;
        $variation_id = $request->variation_id;
        $transaction_pin = $request->transaction_pin;

        $data['transaction_id'] = $this->utility->generateTransactionID(4);
        $data['status']         = 0;
        $data['commission']     = 0;
        $data['payment_method'] = 'WALLET';
        $data['platform']       = 'MOBILE';
        $data['user_id']        = auth()->user()->id;
        $data['payment_ref']       = 'TXC_' . $ref;

        $userID = $this->utility->getUserByID(Auth::id());
        if(!empty($userID) && $userID !== -1) {
            $data['user_id'] = $userID;
        } else {
            return response()->json(['message'=>'Unauthenticated user.'], 422);

        }

        $tvTransaction = \App\Models\TVTransaction::on('mysql::write')->create($data);

        $user = \App\Models\User::on('mysql::read')->where('email', $tvTransaction->email)->first();

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
        $tvProvider = \App\Models\Service::on('mysql::read')->find($tvTransaction->service_id);
        $tvBundle = \App\Models\TVBundle::on('mysql::read')->find($tvTransaction->tv_bundles_id);
        $service = \App\Models\Service::on('mysql::read')->find($tvTransaction->service_id);

        $wallet = Wallet::on('mysql::write')->where('user_id', $user->id)->first();
        $acc = AccountNumber::on('mysql::read')->where([['wallet_id', $user->wallet->id], ['account_name', 'Wallet ID']])->first();


        $current_balance = floatval($user->wallet->balance);
        if($current_balance > floatval($tvTransaction->amount)){
            $new_balance = $current_balance - intval($tvTransaction->amount);
            $wallet = Wallet::on('mysql::write')->where('user_id',$user->id)->first();
            $wallet->update(['balance' => $new_balance]);

            $tvTransaction->update(['amount_paid' => $tvTransaction->amount]);

            $tvTransaction->update([
                'status'            =>  1,
                'amount_paid'       =>  $amountPaid,
                'payment_method'    =>  'WALLET',
            ]);

            $tvTransaction->update(['amount_paid' => $tvTransaction->amount]);

            $response = Http::withHeaders([
                'Content-Type' => "application/json"
            ])->get(env('VTU_DOT_NG_BASE_URL')."tv?username=$username&password=$password&phone=$user->phone&service_id=$service_id&smartcard_number=$smartcard_number&variation_id=$variation_id");

            $new_balance = $current_balance - intval($tvBundle->amount);
            $wallet->update(['balance' => $new_balance]);
            $resp['status'] = 2000;
            $resp['success'] = true;
            $resp['msg'] = 'success';


            $this->saveUserActivity(ActivityType::TV, '', $user->id);

            WalletTransaction::on('mysql::write')->create([
                'wallet_id'=>$user->wallet->id,
                'type'=>'Debit',
                'amount'=>$tvTransaction->amount,
                'description'=>'TV Bill Payment',
                'transfer'=>false,
                'transaction_ref'=>'TXC_' . $ref,
                'reference'=>$tvTransaction->transaction_id,
                'status'=>'success',
            ]);

        }else{
                WalletTransaction::on('mysql::write')->create([
                'wallet_id'=>$user->wallet->id,
                'type'=>'Debit',
                'amount'=>$tvTransaction->amount,
                'description'=>'TV Bill Payment | Insufficient funds. Please TopUp your wallet.',
                'transfer'=>false,
                'reference'=>$tvTransaction->transaction_id,
                'status'=>'failed',
            ]);
            return response()->json([
                "message" => "Insufficient funds. Please TopUp your wallet.",
                'status' => false,
            ], 413);
        }
        return response()->json([
                "message" => "Cable TV subscription successfully delivered",
                'data' => $response['data'],
                'status' => 'success',
            ], 200);


        }catch(ValidationException $e){
            return response()->json(['message'=>$e->getMessage(), 'errors'=>$e->errors()]);
        }
    }


    public function TVBundles()
    {
        try {

            $data = TVBundle::orderBy('created_at','desc')->paginate(50);
            $message = 'data successfully fetched';

            return $this->sendResponse($data,$message);
        }catch (ModelNotFoundException $e) {
            return response()->json(['message' => $e->getMessage()],404);
        } catch(\Exception $e) {
            return response()->json(['message' => $e->getMessage()],500);
        }
    }
}



