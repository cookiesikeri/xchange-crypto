<?php

namespace App\Http\Controllers\Apis;


use App\Mail\TransactionMail;
use App\Models\AccountNumber;
use App\Models\BankTransfer;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Functions;
use App\Traits\ManagesUsers;
use Illuminate\Http\Request;
use App\Interfaces\UserInterface;
use App\Mail\DebitEmail;
use App\Mail\OtpMail;

use App\Models\AirtimeTransaction;
use App\Models\DataTransaction;
use App\Models\Models\OtpVerify;
use App\Models\PaystackRefRecord;
use App\Models\TVTransaction;
use App\Models\User;
use App\Models\UserSecretQAndA;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use App\Models\WalletTransfer;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;
use App\Traits\SendSms;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    use SendSms, ManagesUsers;

    protected $user;
    protected $utility;

    /**
     * UserController constructor.
     * @param UserInterface $user
     */
    public function __construct(UserInterface $user)
    {
        $this->user = $user;
        $this->utility = new Functions();
    }

    public function is_user($user_id)
    {
        return response()->json($this->user->is_user($user_id));
    }

    public function create_user_wallet($user_id)
    {
        return response()->json($this->user->create_user_wallet($user_id));
    }

    public function get_user_wallet_balance($user_id)
    {
        return response()->json($this->user->get_user_wallet_balance($user_id));
    }

    public function user_has_sufficient_wallet_balance($user_id, $amount)
    {
        return response()->json($this->user->user_has_sufficient_wallet_balance($user_id, $amount));
    }

    public function update_user_wallet_balance($user_id, $amount)
    {
        return response()->json($this->user->update_user_wallet_balance($user_id, $amount));
    }

    public function debit_user_wallet($user_id, $amount)
    {
        return response()->json($this->user->debit_user_wallet($user_id));
    }

    public function fund_user_wallet_card(Request $request)
    {
        //return response()->json(['status'=>403, 'message'=>'Service Unavailable!'], 403);
        try{
            $request->validate([
                'reference'=>'required',
                'user_id'=>'required',
            ]);
        }catch(ValidationException $exception){
            return response()->json(['errors'=>$exception->errors(), 'message'=>$exception->getMessage()]);
        }

        $paystack_payment_reference = $request->reference;

        $userId = Auth::id();

        if(!$userId){
            return response()->json(['message'=>'Unauthenticated user. Kindly login.']);
        }
        //$key = config('app.paystack');
        $msg = '';

        // verify the payment

        $user = \App\Models\User::on('mysql::read')->where('id', $userId)->first();

        if(!$user){
            return response()->json(['message'=>'Could not find User.']);
        }

        $checkRef = PaystackRefRecord::where('ref', $paystack_payment_reference)->first();

        if($checkRef && $checkRef->status == 'success'){
            return response()->json(['message'=>'Already processed this transaction.']);
        }elseif (!$checkRef){
            $checkRef = PaystackRefRecord::create([
                'ref'=>$paystack_payment_reference,
                'status'=>'pending',
            ]);
        }

         //$reference ='WALLET-'. $this->user->generate_transaction_reference();

        $verification_status = $this->utility->verifyPaystackPayment($paystack_payment_reference);

        $amount = intval($verification_status['amount'])/100;
        $acc = AccountNumber::on('mysql::read')->where([['wallet_id', $user->wallet->id], ['account_name', 'Wallet ID']])->first();
        $walTransaction = WalletTransaction::on('mysql::write')->create([
            'wallet_id'=>$user->wallet->id,
            'type'=>'Credit',
            'amount'=>$amount,
            'description'=>'Deposit into wallet.',
            'receiver_account_number'=>$acc->account_number,
            'receiver_name'=>$user->name,
            'transfer'=>false,
            'transaction_ref'=>$paystack_payment_reference,
            'transaction_type'=>'card',
        ]);
        if ($verification_status['status'] == -1) {
            // cURL error
            // log as failed transaction
            $msg = 'Paystack payment verification failed to verify wallet funding.';
            $checkRef->update(['status'=>'failed']);
            $walTransaction->update([
                'status'=>'failed',
            ]);
        } else if ($verification_status['status'] == 503) {
            $msg = 'Paystack payment verification was unable to confirm payment.';
            $checkRef->update(['status'=>'failed']);
            $walTransaction->update([
                'status'=>'failed',
            ]);
        } else if ($verification_status['status'] == 404) {
            $msg = 'Unfortunately, transaction reference not found.';
            $checkRef->update(['status'=>'failed']);
            $walTransaction->update([
                'status'=>'failed',
            ]);
        }else if ($verification_status['status'] == 400) {
            $msg = 'Unfortunately, transaction failed.';
            $checkRef->update(['status'=>'failed']);
            $walTransaction->update([
                'status'=>'failed',
            ]);
        } else if ($verification_status['status'] == 100) {
            $msg = 'Paystack payment verification successful. Wallet funded';
            //return $user->wallet->id;
            //$this->user->update_user_wallet_balance(($user->wallet->balance + $amount));
            $newBal = intval($user->wallet->balance) + intval($amount);
            $wallet = Wallet::on('mysql::write')->where('user_id',$user->id)->first();
            $wallet->update(['balance' => $newBal]);
            $walTransaction->update([
                'status'=>'success',
            ]);
            $checkRef->update(['status'=>'success']);

            Mail::to($user->email)
                ->send(new TransactionMail($user->name,$amount));
        }

        return response()->json(['message'=>$msg, 'wallet'=>$wallet]);
    }

    public function fund_user_wallet_transfer(Request $request)
    {
        try{
            $request->validate([
                'reference'=>'required',
                'user_id'=>'required',

            ]);
        }catch(ValidationException $exception){
            return response()->json(['errors'=>$exception->errors(), 'message'=>$exception->getMessage()]);
        }
        $paystack_payment_reference = $request->reference;
        //$amount = $request->amount;
        $userId = $request->user_id;
        //$key = config('app.paystack');
        $msg = '';

        // verify the payment
        try{
            $user = User::on('mysql::write')->findOrFail($userId);
        }catch(ModelNotFoundException $e){
            return response()->json(['message'=>'Could not find User.'], 404);
        }
        if(!$user){
            return response()->json(['message'=>'Could not find User.'], 404);
        }

        $checkRef = PaystackRefRecord::where('ref', $paystack_payment_reference)->first();

        if($checkRef && $checkRef->status == 'success'){
            return response()->json(['message'=>'Already processed this transaction.']);
        }elseif (!$checkRef){
            $checkRef = PaystackRefRecord::create([
                'ref'=>$paystack_payment_reference,
                'status'=>'pending',
            ]);
        }
         //$reference ='WALLET-'. $this->user->generate_transaction_reference();

         $verification_status = $this->utility->verifyPaystackPayment($paystack_payment_reference);

         //return $verification_status;

         $amount = intval($verification_status['amount']);
        if ($verification_status['status'] == -1) {
            // cURL error
            // log as failed transaction
            $msg = 'Transfer failed to verify wallet funding.';
        } else if ($verification_status['status'] == 503) {
            $msg = 'Transfer was unable to be confirm.';
        } else if ($verification_status['status'] == 404) {
            $msg = 'Unfortunately, transaction reference not found.';
        }else if ($verification_status['status'] == 400) {
            $msg = 'Unfortunately, transaction is pending.';
        } else if ($verification_status['status'] == 100) {
            $msg = 'Transfer verification successful.';
            //return $user->wallet->id;
            //$this->user->update_user_wallet_balance(($user->wallet->balance + $amount));
            $newBal = intval($user->wallet->balance) + intval($amount);
            $user->wallet()->update(['balance' => $newBal]);
            $walTransaction = WalletTransaction::on('mysql::write')->create(['wallet_id'=>$user->wallet->id, 'type'=>'Credit', 'amount'=>$amount]);
            Mail::to($user->email)
                ->send(new TransactionMail($user->name,$amount));
        }

        return response()->json(['message'=>$msg]);
    }


    public function setTransactionPin(Request $request){
        try{

            $validator = Validator::make($request->all(), [
                'user_id'=>'required',
                'transaction_pin'=>'required|digits:4'
            ]);

            if ($validator->fails()) {
                return response()->json($validator->errors(), 422);
            }

            $pin = strval($request->transaction_pin);
            $user = User::on('mysql::write')->findOrFail($request->user_id);

            if(!empty($user->transaction_pin) || $user->transaction_pin != null){
                return response()->json(['message'=>'Transaction Pin already set, kindly update it.'], 420);
            }

            if (!$this->ownsRecord($request->get('user_id'))) {
                return response()->json(['message'=>'You are not permitted to change this PIN'], 420);
            }

            $hashPin = Hash::make($pin);

            $update = $user->update(['transaction_pin'=>$hashPin]);

            if(!$update){
                return response()->json(['message'=>'Unable to set Transaction Pin. Please try again.'], 422);
            }

            return response()->json(['message'=>'Transaction Pin set successfully.', 'user'=>$user], 200);
        }catch(ValidationException $ve){
            return response()->json(['message'=>$ve->getMessage(), 'errors'=>$ve->errors()], 422);
        }catch(ModelNotFoundException $me){
            return response()->json(['message'=>'User not found.'], 404);
        }catch(Exception $e){
            return response()->json(['message'=>$e->getMessage()], 422);
        }
    }

    public function sendOTP($user)
    {
        $otp = mt_rand(10000,99999);
        $validity = 10;
        OtpVerify::on('mysql::write')->create([
            'user_id' => $user->id,
            'otp' => $otp,
            'expires_at' => Carbon::now()->addMinutes($validity)
        ]);
        $message = "Hello! Your TaheerXchange Verification Code is $otp. Code is valid for the next ".$validity."minutes.";

        // $this->sendSms($user->phone,$message);
        Mail::to($user->email)->send(new OtpMail($user->name, $otp));

        return "OTP successfully generated";
    }

    public function initChangePin(Request $request){
        try{

            $validator = Validator::make($request->all(), [
                'phone_number' => 'required',
            ]);

            if ($validator->fails()) {
                return response()->json($validator->errors(), 422);
            }

            $phone_number = $request->input('phone_number');
            $user = User::on('mysql::read')->where('phone',$phone_number)->first();
            $ret = $this->sendOtp($user);

            return response()->json(['message'=>$ret]);
        }catch(ModelNotFoundException $me){
            return response()->json(['message'=>'User not found.'], 404);
        }catch(Exception $e){
            return response()->json(['message'=>$e->getMessage()], 422);
        }
    }

    public function getUserSecretQuestion($user_id){
        try{

            $user = User::on('mysql::read')->findOrFail($user_id);

            return response()->json(['message'=>'Success', 'secret_question'=>$user->secret_q_and_a]);
        }catch(ModelNotFoundException $me){
            return response()->json(['message'=>'User not found.'], 404);
        }catch(Exception $e){
            return response()->json(['message'=>$e->getMessage()], 422);
        }
    }

    public function setSecretQandA(Request $request){
        try{

            $validator = Validator::make($request->all(), [
                'user_id' => 'required|uuid',
                'question'=>'required',
                'answer'=>'required',
            ]);

            if ($validator->fails()) {
                return response()->json($validator->errors(), 422);
            }

            $user_id = $request->input('user_id');
            $user = User::on('mysql::read')->findOrFail($user_id);

            UserSecretQAndA::on('mysql::write')->create([
                'user_id'=>$user->id,
                'question'=>$request->question,
                'answer'=>Hash::make($request->answer),
            ]);

            return response()->json(['message'=>'Users secret question and answer saved.']);
        }catch(ModelNotFoundException $me){
            return response()->json(['message'=>'User not found.'], 404);
        }catch(Exception $e){
            return response()->json(['message'=>$e->getMessage()], 422);
        }
    }

    public function updateTransactionPin(Request $request){
        try{

            $validator = Validator::make($request->all(), [
                'user_id'=>'required',
                'secret_answer'=>'required',
                'otp'=>'required',
                'old_pin'=>'required|digits:4',
                'transaction_pin'=>'required|digits:4',
            ]);

            if ($validator->fails()) {
                return response()->json($validator->errors(), 422);
            }

            $user = Auth::user();

            if (!$this->ownsRecord($request->get('user_id'))) {
                return response()->json(['message'=>'You can not change this PIN'], 405);
            }

            $verifyOtp = OtpVerify::on('mysql::read')->where([
                'otp' => $request->otp,
                'user_id' => $request->user_id
            ])->first();

            if(empty($verifyOtp)){
                return response()->json(['errors' => 'OTP does not exist','status' => '404'], 404);
            }

            if(Carbon::now() >= $verifyOtp->expires_at){
                return response()->json(['errors' => 'OTP is no longer valid','status' => '403'], 403);
            }
            if(!Hash::check($request->secret_answer, $user->secret_q_and_a->answer)){
                return response()->json(['message'=>'Answer to secret question incorrect.'], 420);
            }

            if(!Hash::check(trim($request->input('old_pin')), $user->transaction_pin)){
                return response()->json(['message'=>'Old pin entered is incorrect.'], 420);
            }

            $update = $user->update(['transaction_pin'=>Hash::make($request->transaction_pin)]);

            if(!$update){
                return response()->json(['message'=>'Unable to set Transaction Pin. Please try again.'], 422);
            }

            return response()->json(['message'=>'Transaction Pin updated successfully.', 'user'=>$user], 200);
        }catch(ValidationException $ve){
            return response()->json(['message'=>$ve->getMessage(), 'errors'=>$ve->errors()], 422);
        }catch(ModelNotFoundException $me){
            return response()->json(['message'=>'User not found.'], 404);
        }catch(Exception $e){
            return response()->json(['message'=>$e->getMessage()], 422);
        }
    }

    public function allUsersBillTransaction($user_id, $bill)
    {
        try{
            switch($bill){
                case 'airtime':
                    $history = AirtimeTransaction::on('mysql::read')->where('user_id', $user_id)->get();
                    break;
                case 'data':
                    $history = DataTransaction::on('mysql::read')->where('user_id', $user_id)->get();
                    break;
                default:

            }

            return response()->json(['message'=> $bill.' transaction history retrieved.', 'transactions'=>$history]);
        }catch(Exception $e){

            return response()->json(['message'=>$e->getMessage()], 420);
        }
    }

    public function get_user_airtime_transactions($user_id, $paginate, $status)
    {
        return response()->json($this->user->get_user_airtime_transactions($user_id, $paginate, $status));
    }

    public function get_user_all_airtime_transactions($user_id, $status)
    {
        try{
            $history = AirtimeTransaction::on('mysql::read')->where([['user_id', $user_id], ['status', $status]])->get();
            return response()->json(['message'=>'Airtime transaction history retrieved.', 'transactions'=>$history]);
        }catch(Exception $e){
    }
}

    public function get_user_data_transactions($user_id, $paginate, $status)
    {
        return response()->json($this->user->get_user_data_transactions($user_id, $paginate, $status));
    }

    public function get_user_all_data_transactions($user_id, $status)
    {
        try{
            $history = DataTransaction::on('mysql::read')->where([['user_id', $user_id], ['status', $status]])->get();
            return response()->json(['message'=>'Data transaction history retrieved.', 'transactions'=>$history]);
        }catch(Exception $e){
            return response()->json(['message'=>$e->getMessage()], 420);
        }
    }


    public function log_wallet_transaction($user, $amount_entered, $new_balance, $transaction_type, $description, $transaction_status, $transaction_reference)
    {
        return response()->json($this->user->log_wallet_transaction($user, $amount_entered, $new_balance, $transaction_type, $description, $transaction_status, $transaction_reference));
    }

    public function get_vendor_wallet_transaction_history($wallet_id, $chunk)
    {
        return response()->json($this->user->get_vendor_wallet_transaction_history($wallet_id, $chunk));
    }

    public function generate_transaction_reference()
    {
        return response()->json($this->user->generate_transaction_reference());
    }

    public function RequestPhysicalCard(Request $request)
    {
        $this->validate($request, [
            'name' => 'required',
            'address' =>  'nullable|string',
            'phone_number' =>  'nullable|string|max:11'
        ]);


        $response = \App\Models\CardRequestPhysical::on('mysql::write')->create([

            'user_id' => auth()->user()->id,
            'name' => $request->name,
            'address' => $request->address,
            'phone_number' => $request->phone_number,
            'state' => $request->state,
            'lga' => $request->lga,


        ]);

        return response()->json([
            "message" => "Request sent successfully",
            'response' => $response,
            'status' => 'success',
        ], 201);
    }

    public function RequestVirtualCard(Request $request)
    {
        $this->validate($request, [
            'name' => 'required',
            'address' =>  'nullable|string',
            'currency' =>  'nullable|string',
            'card_type' =>  'nullable|string',
            'amount' =>  'nullable|numeric|gt:0'
        ]);


        $response = \App\Models\CardRequestVirtual::on('mysql::write')->create([

            'user_id' => auth()->user()->id,
            'name' => $request->name,
            'address' => $request->address,
            'currency' => $request->currency,
            'card_type' => $request->card_type,
            'amount' => $request->amount,


        ]);

        return response()->json([
            "message" => "Request sent successfully",
            'response' => $response,
            'status' => 'success',
        ], 201);
    }

    public function GetvirtualcardDetails(Request $request)
    {

        return response()->json($request->user()->card_request_virtual);
    }

    public function GetphysicalDetails(Request $request)
    {

        return response()->json($request->user()->card_request_physical);
    }

    public function getReferrals($user_id){
        try{
            $user = User::on('mysql::read')->findOrFail($user_id);
            $accounts = array();

            foreach($user->referrals as $ben){

                //$acc = AccountNumber::on('mysql::read')->where('account_number', $ben['beneficiary_account_number'])->first();
                $ref = User::on('mysql::read')->find($ben->referred_id);
                if($ref && $ref != null){
                    $accounts[] = $ref;
                }
            }

            return response()->json(['message'=>'Referees Retrieved Successfully', 'referrals'=>$accounts,], 200);
        }catch(ModelNotFoundException $me){
            return response()->json(['message'=>'User not found.'], 404);
        }catch(Exception $e){
            return response()->json(['message'=>$e->getMessage()], 404);
        }

    }

    public function getTransferTransactionHistory($user_id){
        try{
            $user = User::on('mysql::read')->findOrFail($user_id);
            $transactions = array();
            $wallTr = WalletTransaction::on('mysql::read')->where([['wallet_id', $user->wallet->id], ['transfer', true]])->get();
            /* foreach($wallTr as $trans){
                $tDay = Carbon::parse($trans['created_at']);
                $today = Carbon::now();
                if($tDay == $today){
                    $transactions[] = $trans;
                }
            } */
//            dispatch(new PeaceAccountCreationJob($user));
            return response()->json(['message'=>'Todays Transactions Retrieved Successfully', 'transactions'=>$wallTr], 200);
        }catch(Exception $e){
            return response()->json(['message'=>$e->getMessage()], 420);
        }
    }

    public function getTodayTransaction($user_id){
        try{
            $user = User::on('mysql::read')->findOrFail($user_id);
            $transactions = array();

            $wallTr = WalletTransaction::on('mysql::read')->where([['wallet_id', $user->wallet->id], ['created_at', Carbon::today()]])->get();

            return response()->json(['message'=>'Todays Transactions Retrieved Successfully', 'transactions'=>$wallTr], 200);
        }catch(Exception $e){
    }
}

    public function getMonthTransaction($user_id, $month){
        try{
            //return Carbon::parse('2021-04-07 13:09:58')->format('F');
            $user = User::on('mysql::read')->findOrFail($user_id);
            $transactions = array();

            $wallTr = WalletTransaction::on('mysql::read')->where('wallet_id', $user->wallet->id)->get();
            foreach($wallTr as $trans){
                $tDay = Carbon::parse($trans['created_at']);
                $today = Carbon::now();
                if($tDay == $today){
                    $transactions[] = $trans;
                }
            }
            return response()->json(['message'=>'Todays Transactions Retrieved Successfully', 'transactions'=>$wallTr], 200);
        }catch(Exception $e){

            return response()->json(['message'=>$e->getMessage()], 420);
        }
    }

    public function index()
    {
        $users = User::select('users.name', 'users.email', 'users.phone', 'users.account_type_id AS kyc_level', 'account_numbers.account_number', 'wallets.balance', 'users.created_at', 'users.updated_at')
            ->join('wallets', 'users.id', '=', 'wallets.user_id')->join('account_numbers', 'wallets.id', '=', 'account_numbers.wallet_id')->where('account_numbers.account_name', 'Wallet ID')->paginate(10);
        return response()->json(['success'=>true, 'data' =>$users, 'message' => 'users fetched successfully']);
    }

    public function indexNull()
    {
        $users = User::select('users.name', 'users.email', 'users.phone', 'users.account_type_id AS kyc_level', 'account_numbers.account_number', 'wallets.balance', 'users.created_at', 'users.updated_at')
            ->join('wallets', 'users.id', '=', 'wallets.user_id')->join('account_numbers', 'wallets.id', '=', 'account_numbers.wallet_id')->whereNull('Wallet ID')->paginate(10);
        return response()->json(['success'=>true, 'data' =>$users, 'message' => 'users fetched successfully']);
    }


}
