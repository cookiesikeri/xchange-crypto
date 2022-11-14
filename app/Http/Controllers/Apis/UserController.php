<?php

namespace App\Http\Controllers\Apis;

use App\Jobs\PeaceAccountCreationJob;
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
use App\Models\LoanBalance;
//use App\Models\LoanTransaction;
use App\Models\Models\OtpVerify;
use App\Models\PaystackRefRecord;
use App\Models\PosLocation;
use App\Models\PosRequest;
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

    public function edit_profile(Request $request)
    {
        $data = array(
            'name' =>  $request->name,
            'address' =>  $request->address,
            'email' =>  $request->email,
            'phone_number' =>  $request->phone_number,
        );

        $validator = Validator::make($data, [
            'name' =>  'nullable|string',
            'address' =>  'nullable|string',
            'email' =>  'nullable|string',
            'phone_number' =>  'nullable|string|max:11'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors());
        }

        $user = $this->user->is_user($request->id);

        if (is_int($user)) {
            return response()->json("User not found.");
        }

        if (strlen($data['name']) <= 1) {
            $data['name'] = $user->name;
        }

        // if(strlen($data['email']) <= 1) {
        //     $data['email'] = $user->email;
        // }

        if(strlen($data['address']) <= 1) {
            $data['address'] = $user->address;
        }

        if (strlen($data['phone_number']) <= 1) {
            $data['phone_number'] = $user->phone_number;
        }

        $update = $user->update($data);

        return response()->json($update);
    }

    public function edit_logon(Request $request)
    {
        $u = $request->u;
        $o = $request->o;
        $n = $request->n;
        $r = $request->r;

        if ($n !== $r) {
            return response()->json("eq");
        }

        $validator = Validator::make($request->all(), [
            'o' =>  'required|string',
            'n' =>  'required|string',
            'r' =>  'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors());
        }

        $user = $this->user->is_user($u);

        if (is_int($user)) {
            return response()->json("User not found.");
        }

        $creds = array('email'  => $user->email, 'password' => $o);

        if (auth()->attempt($creds)) {
            // Authentication passed...
            $user->update([
                'password'  =>  Hash::make($n)
            ]);
            return response()->json("200");
        } else {
            return response()->json("404");
        }
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

        return response()->json(['message'=>$msg, 'wallet'=>$wallet,]);
    }

    public function getBanksList(){
        $response = Http::withHeaders([
            'Authorization' => 'Bearer '.env('PAYSTACK_SECRET_KEY')
        ])->get(env('PAYSTACK_BASE_URL').'/bank');

        return response()->json(['banks'=>$response['data']]);
    }


    public function verifyAccountNumber(Request $request)
    {
        try{
            $request->validate([
                'account_number'=>'required',
                'bank_code' => 'required'
            ]);

            $account_number = $request->account_number;
            $bank_code = $request->bank_code;


            $response = Http::withHeaders([
                'Authorization' => 'Bearer '.env('PAYSTACK_SECRET_KEY'),
                'Content-Type' => "application/json"
            ])->get(env('PAYSTACK_SECRET_KEY')."/bank/resolve?account_number=$account_number&bank=$bank_code");

            return response()->json(['account'=> $response['data']]);
        }catch(Exception $e){
            return response()->json(['message'=>$e->getMessage()], 422);
        }

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
                case 'power':
                    $history = PowerTransaction::on('mysql::read')->where('user_id', $user_id)->get();
                    break;
                case 'tv':
                    $history = TVTransaction::on('mysql::read')->where('user_id', $user_id)->get();
                    break;
                default:

            }

            return response()->json(['message'=> $bill.' transaction history retrieved.', 'transactions'=>$history]);
        }catch(Exception $e){
            Http::post(env('VFD_HOOK_URL'),[
                'text' => $e->getMessage(),
                'username' => 'UserController - Get user airtime transaction history method (api.transave.com.ng) ',
                'icon_emoji' => ':boom:'
            ]);
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
            Http::post(env('VFD_HOOK_URL'),[
                'text' => $e->getMessage(),
                'username' => 'UserController - Get user airtime transaction history method (api.transave.com.ng) ',
                'icon_emoji' => ':boom:'
            ]);
            return response()->json(['message'=>$e->getMessage()], 420);
        }
    }

    public function get_user_data_transactions($user_id, $paginate, $status)
    {
        return response()->json($this->user->get_user_data_transactions($user_id, $paginate, $status));
    }

    public function tst($data){

    }

    public function get_user_all_data_transactions($user_id, $status)
    {
        try{
            $history = DataTransaction::on('mysql::read')->where([['user_id', $user_id], ['status', $status]])->get();
            return response()->json(['message'=>'Data transaction history retrieved.', 'transactions'=>$history]);
        }catch(Exception $e){
            Http::post(env('VFD_HOOK_URL'),[
                'text' => $e->getMessage(),
                'username' => 'UserController - Get user data transaction history method (api.transave.com.ng) ',
                'icon_emoji' => ':boom:'
            ]);
            return response()->json(['message'=>$e->getMessage()], 420);
        }
    }

    public function get_user_all_power_transactions($user_id, $status)
    {
        try{
            $history = PowerTransaction::on('mysql::read')->where([['user_id', $user_id], ['status', $status]])->get();
            return response()->json(['message'=>'Power transaction history retrieved.', 'transactions'=>$history]);
        }catch(Exception $e){
            Http::post(env('VFD_HOOK_URL'),[
                'text' => $e->getMessage(),
                'username' => 'UserController - Get user power transaction history method (api.transave.com.ng) ',
                'icon_emoji' => ':boom:'
            ]);
            return response()->json(['message'=>$e->getMessage()], 420);
        }
    }

    public function get_user_all_tv_transactions($user_id, $status)
    {
        try{
            $history = TVTransaction::on('mysql::read')->where([['user_id', $user_id], ['status', $status]])->get();
            return response()->json(['message'=>'TV transaction history retrieved.', 'transactions'=>$history]);
        }catch(Exception $e){
            Http::post(env('VFD_HOOK_URL'),[
                'text' => $e->getMessage(),
                'username' => 'UserController - Get user tv transaction history method (api.transave.com.ng) ',
                'icon_emoji' => ':boom:'
            ]);
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

    public function PosRequest(Request $request)
    {
        try{
            $validator = Validator::make($request->all(), [
                'pos_locations'=>'required',
                'no_of_pos' => 'required|gt:0',
                'means_of_id' => 'required',
                'id_number' => 'required',
                'nearest_bus_stop'=>'required',
                'occupation' => 'required',
                'has_business' => 'required|boolean',
                'business_name' => 'exclude_unless:has_business,true|required|string',
                'business_license' => 'exclude_unless:has_business,true|required|file',
                'business_type'=>'exclude_unless:has_business,true|required',
                'business_reg_num'=>'exclude_unless:has_business,true|required',
                'other_doc' => 'nullable|file',
            ]);

            if ($validator->fails()) {
                return response()->json($validator->errors());
            }

            if($request->has_business && ($request->business_name == null || $request->business_license == null || $request->business_type == null || $request->business_reg_num == null)){
                return response()->json(['message'=>'Missing Information! Business Name or Business License or Business Type or Business Reg Number.'], 422);
            }

            $user_id = Auth::id();
            $idPhoto = '';
            $blPhoto = '';
            $utilityPhoto = '';
            $otherPhoto = '';
            $dob = date('Y-m-d', strtotime($request->dob));

            if(!$user_id || $user_id == null){
                return response()->json(['message'=>'Unauthenticated User! Kindly login.'], 422);
            }

            $user = User::find($user_id);

            if($user->account_type_id != 3)
            {
                return response()->json(['message'=>'Kindly update your KYC.'], 422);
            }

            $exists = PosRequest::on('mysql::read')->where('user_id', $user_id)->first();

            /* if($exists){
                return response()->json(['message'=>'Request already Sent.'], 422);
            } */

            if($request->hasFile('business_license')){
                $blPhoto = $this->utility->saveFile($request->file('business_license'), 'pos_request/users', 'business-license-photo');
            }

            if($request->hasFile('other_doc')){
                $otherPhoto = $this->utility->saveFile($request->file('other_doc'), 'pos_request/users', 'other-doc-photo');
            }

            $data = \App\Models\PosRequest::on('mysql::write')->create([
                'no_of_pos' => $request->no_of_pos,
                'status' => 'processing',
                'account_number' => 0,
                'bank_name' => '',
                'other_doc' => $otherPhoto,
                'utility_billdoc' => $utilityPhoto,
                'country' => '',
                'state' => $request->state,
                'lga' => $request->lga,
                'city' => $request->city,
                'means_of_id' => $request->means_of_id,
                'identification_doc' => $idPhoto,
                'id_number' => $request->id_number,
                'passport' => '',
                'occupation' => $request->occupation,
                'job_title' => '',
                'business_licence' => $blPhoto,
                'user_id'=> $user_id,
                'nearest_bus_stop'=>$request->nearest_bus_stop,
                'has_business'=>$request->has_busisness,
                'business_name'=>$request->business_name,
                'business_type'=>$request->business_type,
                'business_reg_num'=>$request->business_reg_num,
            ]);

            $pos_locs = json_decode($request->input('pos_locations'), true);

            foreach($pos_locs as $pos_loc)
            {
                PosLocation::on('mysql::write')->create([
                    'pos_request_id'=>$data->id,
                    'state'=> $pos_loc['state'],
                    'address'=> $pos_loc['address'],
                    'city'=> $pos_loc['city'],
                ]);
            }
            $data->pos_locations;

            return response()->json([
                "message" => "Your application has been submitted successfully",
                'response' => $data,
                'status' => 'success',
            ], 201);

        }catch(ValidationException $ve){
            Http::post(env('VFD_HOOK_URL'),[
                'text' => $ve->getMessage().' '.$ve->errors(),
                'username' => 'User Controller - POS request method (api.transave.com.ng) ',
                'icon_emoji' => ':boom:'
            ]);
            return response()->json(['message'=>$ve->getMessage(), 'errors'=>$ve->errors()], 420);
        }
        catch(Exception $e){
            Http::post(env('VFD_HOOK_URL'),[
                'text' => $e->getMessage(),
                'username' => 'User Controller - POS request method (api.transave.com.ng) ',
                'icon_emoji' => ':boom:'
            ]);
            return response()->json(['message'=>$e->getMessage()], 420);
        }
    }

    public function usersPosRequest($user_id)
    {
        try{
            $posReqs = PosRequest::on('mysql::read')->where('user_id', $user_id)->get();
            if(!$posReqs){
                return response()->json(['message'=>'No requests found for this user!'], 404);
            }
            return response()->json(['message'=>'Users POS Requests', 'requests'=>$posReqs]);
        }catch(Exception $e){
            Http::post(env('VFD_HOOK_URL'),[
                'text' => $e->getMessage(),
                'username' => 'User Controller - Users sent POS requests method (api.transave.com.ng) ',
                'icon_emoji' => ':boom:'
            ]);
            return response()->json(['message'=>$e->getMessage()], 420);
        }
    }

    public function LoanHistory(Request $request)
    {

        return response()->json($request->user()->loans);
    }

    public function GetvirtualcardDetails(Request $request)
    {

        return response()->json($request->user()->card_request_virtual);
    }

    public function GetphysicalDetails(Request $request)
    {

        return response()->json($request->user()->card_request_physical);
    }

    public function LoanOffer()
    {
        return response()->json(\App\Models\LoanOffer::orderBy('id', 'desc')->get());
    }

    public function saveBeneficiary(Request $request){
        try{
            $request->validate([
                'user_id'=>'required|uuid',
                'account_number'=>'required|numeric',
                'account_type'=>'required|string',
            ]);

            $exists = Beneficiaries::on('mysql::read')->where([['user_id', $request->user_id], ['beneficiary_account_number', $request->account_number], ['account_type',$request->account_type]])->first();

            if($exists){
                return response()->json(['message'=>'Beneficiary Already Saved.']);
            }

            $ben = Beneficiaries::on('mysql::write')->create([
                'user_id'=>$request->user_id,
                'beneficiary_account_number'=>$request->account_number,
                'account_type'=>$request->account_type,
            ]);

            return response()->json(['message'=>'Beneficairy Saved Successfully', 'beneficiary'=>$ben], 200);
        }catch(ModelNotFoundException $me){
            return response()->json(['message'=>''], 404);
        }catch(Exception $e){
            return response()->json(['message'=>$e->getMessage()], 404);
        }

    }

    public function removeBeneficiary(Request $request){
        try{
            $request->validate([
                'user_id'=>'required|uuid',
                'account_number'=>'required|numeric',
            ]);

            $exists = Beneficiaries::on('mysql::read')->where([['user_id', $request->user_id], ['beneficiary_account_number', $request->account_number]])->first();

            if(!$exists){
                return response()->json(['message'=>'Beneficiary Not Found.'], 404);
            }

            $check = $exists->delete();

            return response()->json(['message'=>'Beneficairy Removed Successfully', 'removed'=>$check], 200);
        }catch(ModelNotFoundException $me){
            return response()->json(['message'=>''], 404);
        }catch(Exception $e){
            return response()->json(['message'=>$e->getMessage()], 404);
        }

    }

    public function getBeneficiaries($user_id){
        try{
            $user = User::on('mysql::read')->findOrFail($user_id);
            $accounts = array();

            foreach($user->beneficiaries as $ben){

                $acc = AccountNumber::on('mysql::read')->where('account_number', $ben['beneficiary_account_number'])->first();
                $user = User::on('mysql::read')->find($acc->wallet->user_id);
                if(!$user){
                    $user = Business::on('mysql::read')->find($acc->wallet->user_id);
                }
                $acc['owner_name'] = $user->name;
                $ben['account_details'] = $acc;

                $accounts[] = $ben;
            }

            return response()->json(['message'=>'Beneficairies Retrieved Successfully', 'beneficiaries'=>$accounts], 200);
        }catch(ModelNotFoundException $me){
            return response()->json(['message'=>'User not found.'], 404);
        }catch(Exception $e){
            return response()->json(['message'=>$e->getMessage()], 404);
        }

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
            Http::post(env('VFD_HOOK_URL'), [
                'text' => $e->getMessage(),
                'username' => 'UserController - Get transaction history method (api.transave.com.ng) ',
                'icon_emoji' => ':boom:'
            ]);
            return response()->json(['message'=>$e->getMessage()], 420);
        }
    }

    public function getTodayTransaction($user_id){
        try{
            $user = User::on('mysql::read')->findOrFail($user_id);
            $transactions = array();

            $wallTr = WalletTransaction::on('mysql::read')->where([['wallet_id', $user->wallet->id], ['created_at', Carbon::today()]])->get();
            /* foreach($wallTr as $trans){
                $tDay = Carbon::parse($trans['created_at']);
                $today = Carbon::now();
                if($tDay == $today){
                    $transactions[] = $trans;
                }
            } */
            return response()->json(['message'=>'Todays Transactions Retrieved Successfully', 'transactions'=>$wallTr], 200);
        }catch(Exception $e){
            Http::post(env('VFD_HOOK_URL'),[
                'text' => $e->getMessage(),
                'username' => 'UserController - Get Todays transactions method (api.transave.com.ng) ',
                'icon_emoji' => ':boom:'
            ]);
            return response()->json(['message'=>$e->getMessage()], 420);
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
            Http::post(env('VFD_HOOK_URL'),[
                'text' => $e->getMessage(),
                'username' => 'UserController - Get Todays transactions method (api.transave.com.ng) ',
                'icon_emoji' => ':boom:'
            ]);
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
