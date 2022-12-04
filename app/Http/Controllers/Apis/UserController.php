<?php

namespace App\Http\Controllers\Apis;


use App\Mail\TransactionMail;
use App\Models\AccountNumber;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Functions;
use App\Traits\ManagesUsers;
use Illuminate\Http\Request;
use App\Interfaces\UserInterface;
use App\Mail\DebitEmail;
use App\Mail\OtpMail;

use App\Models\AirtimeTransaction;
use App\Models\BankTransfer;
use App\Models\DataTransaction;
use App\Models\Models\OtpVerify;
use App\Models\PaystackRefRecord;
use App\Models\PowerTransaction;
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
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    use  ManagesUsers;

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
    public function get_user_power_transactions($user_id, $paginate, $status)
    {
        return response()->json($this->user->get_user_power_transactions($user_id, $paginate, $status));
    }

    public function get_user_tv_transactions($user_id, $paginate, $status)
    {
        return response()->json($this->user->get_user_tv_transactions($user_id, $paginate, $status));
    }

    public function get_user_all_power_transactions($user_id, $status)
    {
        try{
            $history = PowerTransaction::on('mysql::read')->where([['user_id', $user_id], ['status', $status]])->get();
            return response()->json(['message'=>'Power transaction history retrieved.', 'transactions'=>$history]);
        }catch(Exception $e){
            return response()->json(['message'=>$e->getMessage()], 420);
        }
    }

    public function get_user_all_tv_transactions($user_id, $status)
    {
        try{
            $history = TVTransaction::on('mysql::read')->where([['user_id', $user_id], ['status', $status]])->get();
            return response()->json(['message'=>'TV transaction history retrieved.', 'transactions'=>$history]);
        }catch(Exception $e){
            return response()->json(['message'=>$e->getMessage()], 420);
        }
    }

    public function get_user_btc_address($user_id)
    {
        return response()->json($this->user->get_user_btc_address($user_id));
    }
    public function get_user_eth_address($user_id)
    {
        return response()->json($this->user->get_user_eth_address($user_id));
    }
    public function get_user_litecoin_address($user_id)
    {
        return response()->json($this->user->get_user_litecoin_address($user_id));
    }
    public function get_user_polygon_address($user_id)
    {
        return response()->json($this->user->get_user_polygon_address($user_id));
    }
    public function get_user_bnb_address($user_id)
    {
        return response()->json($this->user->get_user_bnb_address($user_id));
    }
    public function get_user_dogecoin_address($user_id)
    {
        return response()->json($this->user->get_user_dogecoin_address($user_id));
    }
    public function log_wallet_transaction($user, $amount_entered, $new_balance, $transaction_type, $description, $transaction_status, $transaction_reference)
    {
        return response()->json($this->user->log_wallet_transaction($user, $amount_entered, $new_balance, $transaction_type, $description, $transaction_status, $transaction_reference));
    }

    public function generate_transaction_reference()
    {
        return response()->json($this->user->generate_transaction_reference());
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

            return response()->json(['message'=>'Transfer Transactions Retrieved Successfully', 'transactions'=>$wallTr], 200);
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
            return response()->json(['message'=>'This  Transactions Retrieved Successfully', 'transactions'=>$wallTr], 200);
        }catch(Exception $e){

            return response()->json(['message'=>$e->getMessage()], 420);
        }
    }

    public function transferToBankAcc(Request $request)
    {
        try{
            $request->validate([
                'amount'=>'required|numeric|gt:0',
                'recipient'=>'required',
                'reason'=>'nullable|string',
                'user_id'=>'required',
                'pin'=>'required|integer|numeric'
            ]);
        }catch(ValidationException $e){
            return response()->json(['message'=>$e->getMessage(), 'errors'=>$e->errors()], 422);
        }

        $amount = $request->amount;
        $amount = intval($amount) * 100;
        $rep = $request->recipient;
        $reason = $request->reason;
        $userId = $request->user_id;
        $pin = $request->pin;

        try{
            $user = User::on('mysql::read')->findOrFail($userId);
        }catch(ModelNotFoundException $e){
            return response()->json(['message'=>'User not found.'], 404);
        }


        if(empty($user->transaction_pin)){
            return response()->json(['message'=>'Transaction Pin not set.'], 422);
        }

        if(!Hash::check($pin, $user->transaction_pin))
        {
            return response()->json(['message'=>'Incorrect Pin!'], 404);
        }

        if($user->wallet->balance < ($amount/100)){
            return response()->json(['message'=>'Insufficient Balance.'], 420);
        }

        $res = $this->utility->paystackTransfer($amount, $rep, $reason);

        //return $res;

        if(!$res['error'])
        {
            //return $res['data']['data']['id'];
            try{
                $bnkTransfer = BankTransfer::on('mysql::write')->create([
                    'sender'=>$user->wallet->id,
                    'status'=> $res['data']['data']['status'],
                    'transfer_code'=> $res['data']['data']['transfer_code'],
                    'transfer_id'=>$res['data']['data']['id']
                ]);
            }catch(Exception $e){
                return response()->json(['message'=>$e->getMessage()], 422);
            }
            return response()->json(['message'=>'Transfer Initiated Successfully.', 'details'=>$res['data']['data']], 200);
        }else{
            return response()->json(['message'=>$res['message']], 420);
        }

    }

    public function transferStatus(Request $request){
        try{
            $request->validate([
                'id_or_code'=>'required',
                'user_id'=>'required'
            ]);
        }catch(ValidationException $e){
            return response()->json(['message'=>$e->getMessage(), 'errors'=>$e->errors()], 422);
        }
        $data = array();
        $idOrCode = $request->id_or_code;
        $userId = $request->user_id;
        $msg = 'Could not fetch transfer.';

        $url = 'https://api.paystack.co/transfer/'. $idOrCode;
        //return $url;
        //$url = urlencode($init_url);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt(
            $ch, CURLOPT_HTTPHEADER, ['Authorization: Bearer ' . env('PAYSTACK_SECRET_KEY')]
        );
        //curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        //curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        $request = curl_exec($ch);

        //return $request;
        if(curl_errno($ch)) {
            $verified = curl_errno($ch);
            //Log::info('cURL error occured while trying to verify payment.');
            //Log::error(curl_error($ch));
            $msg = curl_error($ch);
            // verification failed
            $verified = -1;
            return response()->json(['message'=>$msg], 420);
        }else {
            //return $request;
            if ($request) {
                $msg = 'Transafer retrieved.';
                $result = json_decode($request, true);

                $data =  $result['data'];
                //return $data;
                try{
                    $bnkTransfer = BankTransfer::on('mysql::write')->where([['transfer_id', $data['id']], ['transfer_code', $data['transfer_code']]])->get()->first();
                }catch(ModelNotFoundException $e){
                    curl_close($ch);
                    return response()->json(['message'=>'Bank transfer record not found.'], 404);
                }

                if(!$bnkTransfer){
                    curl_close($ch);
                    return response()->json(['message'=>'Bank transfer record not found.'], 404);
                }
                $bnkTransfer->update(['status'=>$data['status']]);

                if($data['status'] == 'success'){

                    try{
                        $user = User::on('mysql::write')->findOrFail($userId);
                    }catch(ModelNotFoundException $e){
                        curl_close($ch);
                        return response()->json(['message'=>'User not found'],);
                    }

                    if(!$user){
                        curl_close($ch);
                        return response()->json(['message'=>'Could not find User.'], 404);
                    }
                    $amount = intval($data['amount'])/100;
                    $newBal = $user->wallet->balance - $amount;

                    $user->wallet()->update(['balance'=>$newBal]);

                    $debit = WalletTransaction::on('mysql::write')->create(['wallet_id'=>$user->wallet->id, 'type'=>'Debit', 'amount'=>$amount]);
                    $walTransfer = WalletTransfer::on('mysql::write')->create([
                        'sender'=>$user->wallet->id,
                        'receiver'=>$data['recipient']['details']['account_number'].'-'.$data['recipient']['details']['bank_name'],
                        'amount'=>$data['amount'],
                        'description'=>$data['reason'],
                    ]);
                    curl_close($ch);
                    return response()->json(['message'=>'Transfer Successful.', 'data'=>$data], 200);

                }elseif($data['status'] == 'otp'){
                    $msg = 'OTP needed to complete transfer.';
                }elseif($data['status'] == 'pending'){
                    $msg = 'Transfer pending.';
                }


            } else {
                // $resp['msg'] = 'Unable to verify transaction!';
                $verified = 503;
            }
        }
         curl_close($ch);
        return response()->json(['message'=>$msg, 'data'=>$data]);
    }

    public function sentTransferHistory($user_id){
        try{
            $user = User::on('mysql::read')->findOrFail($user_id);

            $sent = WalletTransaction::on('mysql::read')->where([['wallet_id', $user->wallet->id], ['type', 'Debit'], ['transfer', true]])->get();

            if(!$sent){
                return response()->json(['message'=>'Users transfer history not found.'], 404);
            }

            return response()->json(['message'=>'Users sent transfer history retrieved.', 'history'=>$sent], 200);
        }catch(ModelNotFoundException $em){
            return response()->json(['message'=>'User not found'], 404);
        }catch(Exception $e){
            return response()->json(['message'=>$e->getMessage()], 422);
        }
    }

    public function receivedTransferHistory($user_id){
        try{
            $user = User::on('mysql::read')->findOrFail($user_id);

            $sent = WalletTransaction::on('mysql::read')->where([['wallet_id', $user->wallet->id], ['type', 'Credit'], ['transfer', true]])->get();

            if(!$sent){
                return response()->json(['message'=>'Users transfer history not found.'], 404);
            }

            return response()->json(['message'=>'Users received transfer history retrieved.', 'history'=>$sent], 200);
        }catch(ModelNotFoundException $em){
            return response()->json(['message'=>'User not found'], 404);
        }catch(Exception $e){
            return response()->json(['message'=>$e->getMessage()], 422);
        }
    }

    public function walletToWalletTransfer(Request $request)
    {
        try{
            $validator = Validator::make($request->all(), [
                'user_id'=>'required',
                'account_number'=>'required',
                'amount'=>'required|numeric|gt:0',
                'description'=>'required',
                'pin'=>'required',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors'=>$validator->errors()], 400);
            }

            //$userId = $request->user_id;
            $userId = Auth::id();
            $accNum = $request->account_number;
            $desc = $request->description;
            $amount = intval($request->amount);
            $pin = $request->pin;

            $transfer = $this->executeWalletTransfer($userId, $accNum, $desc, $amount, $pin);

            return response()->json(['message'=>$transfer['message']], $transfer['status']);
        }catch(Exception $e){
            return response()->json(['message'=>$e->getMessage()], 422);
        }

    }


    public function executeWalletTransfer($userId, $accNum, $desc, $amount, $pin){
        try{
            $user = User::on('mysql::write')->findOrFail($userId);
        }catch(ModelNotFoundException $e){
            return array('message'=>'User not found.', 'status'=> 404);
        }

        if(!$user)
        {
            return array('message'=>'User not found', 'status'=> 404);
        }

        try{
            $userAcc = AccountNumber::on('mysql::read')->where([['wallet_id', $user->wallet->id], ['account_name', 'Wallet ID']])->get()->first();
        }catch(ModelNotFoundException $e){
            return array('message'=>'Senders Wallet not found.', 'status'=>404);
        }

        try{
            $receiver = AccountNumber::on('mysql::write')->where('account_number', $accNum)->get()->first();
        }catch(ModelNotFoundException $e){
            return array('message'=>'Receivers Wallet not found.', 'status'=>404);
        }

        if(!$receiver)
        {
            return array('message'=>'Receiving account not found. Please check the Account Number and try again.','status'=>404);
        }

        if($receiver->wallet_id == $user->wallet->id){
            return array('message'=>'You cannot transafer to your wallet', 'status'=>420);
        }

        $recWall = $receiver->wallet;
        $recAcc = User::on('mysql::read')->find($recWall->user_id);
        $recEmail = $recAcc->email;
        if(!$recAcc){
            $recAcc = User::on('mysql::read')->find($recWall->id);
            $recEmail = $recAcc->user->email;
            if(!$recAcc){
                return array('message'=>'Account owner not found. Please check account number.', 'status'=>404);
            }
        }

        $businessWT = WalletTransaction::on('mysql::write')->create([
            'wallet_id'=>$user->wallet->id,
            'type'=>'Debit',
            'amount'=>$amount,
            'sender_account_number'=> $userAcc->account_number,
            'sender_name'=>$user->name,
            'receiver_name'=>$recAcc->name,
            'receiver_account_number'=>$receiver->account_number,
            'description'=>$desc,
            'bank_name'=>'Transave',
            'transfer'=>true,
            'transaction_type'=>'wallet'
        ]);

        //return $receiver->wallet;
        if(empty($user->transaction_pin)){
            $businessWT->update([
                'status'=>'failed',
            ]);
            return array('message'=>'Transaction Pin not set.', 'status'=> 422);
        }

        if(!Hash::check($pin, $user->transaction_pin))
        {
            $businessWT->update([
                'status'=>'failed',
            ]);
            return array('message'=>'Incorrect Pin!','status'=> 404);
        }

        if($user->wallet->balance < $amount || $amount <= 0){
            $businessWT->update([
                'status'=>'failed',
            ]);
            return array('message'=>'Insufficient balance.','status'=>420);
        }

        $senderNewBal = intval($user->wallet->balance) - $amount;
        $user->wallet()->update(['balance'=>$senderNewBal]);
        $recNewBal = intval($receiver->wallet->balance) + $amount;
        $receiver->wallet()->update(['balance'=>$recNewBal]);
        $wtw = WalletTransfer::on('mysql::write')->create(['sender'=>$user->wallet->id, 'receiver'=>$receiver->wallet->id, 'amount'=>$amount, 'description'=>$desc]);

        $userWT = WalletTransaction::on('mysql::write')->create([
            'wallet_id'=>$receiver->wallet->id,
            'type'=>'Credit',
            'amount'=>$amount,
            'sender_account_number'=> $userAcc->account_number,
            'sender_name'=>$user->name,
            'receiver_name'=>$recAcc->name,
            'receiver_account_number'=>$receiver->account_number,
            'description'=>$desc,
            'bank_name'=>'Transave',
            'transfer'=>true,
        ]);

        $businessWT->update([
            'status'=>'success',
        ]);

        Mail::to($user->email)->send(new DebitEmail($user->name,$amount, $businessWT,$user->wallet, $userAcc));
        Mail::to($recEmail)->send(new TransactionMail($recAcc->name,$amount));

        return array('message'=>'Transfer Successful.','status'=>200);
    }

    public function multiWalletToWalletTransfer(Request $request){

        try{
            $response = array();
            $totalPaid = 0;
            $validator = Validator::make($request->all(), [
                'pin'=>'required|numeric',
                'wallets'=>'required|array',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors'=>$validator->errors()], 400);
            }

            //$userId = $request->wallets[0]['user_id'];
            $userId = Auth::id();
            $pin = $request->pin;

            $user = User::on('mysql::read')->findOrFail($userId);

            if(empty($user->transaction_pin)){
                return response()->json(['message'=>'Transaction Pin not set.', 'status'=> 422], 422);
            }

            if(!Hash::check($pin, $user->transaction_pin))
            {
                return response()->json(['message'=>'Incorrect Pin!','status'=> 420], 420);
            }

            $totalAmount = 0;
            foreach($request->wallets as $wallet){
                $totalAmount = $totalAmount + intval($wallet['amount']);
            }

            if($user->wallet->balance < $totalAmount || $totalAmount <= 0){
                return response()->json(['message'=>'Insufficient funds!','status'=> 420], 420);
            }

            foreach($request->wallets as $wallet){
                $userId = $wallet['user_id'];
                $accNum = $wallet['account_number'];
                $desc = $wallet['reason'];
                $amount = intval($wallet['amount']);
                $name = $wallet['account_name'];

                $transfer = $this->executeWalletTransfer($userId, $accNum, $desc, $amount, $pin);
                $transfer['account_number'] = $accNum;
                $transfer['account_name'] = $name;
                $transfer['sender'] = $userId;

                $response[] = $transfer;

            }

            return response()->json(['message'=>'Successful','status'=>200, 'results'=>$response]);
        }catch(Exception $e){
            return response()->json(['message'=>$e->getMessage()], 422);
        }

    }

    public function verifyWalletAccountNumber($account_number){
        try{

            $account = AccountNumber::on('mysql::read')->where('account_number', $account_number)->first();
            if(!$account){
                return response()->json(['message'=>'Account owner not found. Please check account number.'], 404);
            }
            $wallet = $account->wallet;

            $user = User::on('mysql::read')->find($wallet->user_id);


            return response()->json([ 'status' => true, 'message' => 'Account verified Successfully', 'user' => $user ], 200);

        }catch(Exception $e){
            return response()->json(['message'=>$e->getMessage()], 422);
        }
    }

    public function verifyBVN($bvn, $acct, $code){
        //return 'in pst';
        $url = "https://api.paystack.co/bvn/match";
        $fields = [
            'bvn'=>$bvn,
            'account_number'=>$acct,
            'bank_code'=>$code,
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


}
