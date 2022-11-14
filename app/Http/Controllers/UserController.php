<?php

namespace App\Http\Controllers;

use App\Enums\AccountRequestAction;
use App\Enums\AccountRequestType;
use App\Enums\ActivityType;
use App\Enums\ShutdownLevel;
use App\Enums\TransactionType;
use App\Mail\CreditEmail;
use App\Mail\OnboardingMail;
use App\Mail\TransactionMail;
use App\Models\AccountNumber;
use App\Models\AccountRequest;
use App\Models\CustomerValidation;
use App\Models\Models\InviteeUser;
use App\Models\Models\OtpVerify;
use App\Models\User;
use App\Models\Wallet;
use App\Traits\ManagesResponse;
use App\Traits\ManagesUploads;
use App\Traits\ManagesUsers;
use App\Traits\SendSms;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use App\Models\WalletTransaction;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\File;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use App\Traits\UploadImage;
use App\Jobs\UserActivityJob;
use App\Mail\OtpMail;
use App\Models\ReferralCode;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Validator as FacadesValidator;
use Illuminate\Validation\Validator as ValidationValidator;

class UserController extends Controller
{
    use UploadImage, SendSms, ManagesResponse, ManagesUsers;

    protected $jwt;
    protected $utility;

    public function __construct(JWTAuth $jwt)
    {
        $this->jwt = $jwt;
        $this->utility = new Functions();
    }

    public function register(Request $request, $referral_code=null)
    {
        $validator = Validator::make($request->all(), [
            'name'         => 'required|string|between:2,100',
            'email'         => 'required|string|unique:users',
            'phone' => 'required|string|unique:users',
            'password'     => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        try {
            $user                 = new InviteeUser();
            $user->name           = $request->input('name');
            $user->phone   = $request->input('phone');
            $user->email   = $request->input('email');
            $user->password       = $request->input('password');

            $user->save();

            $this->sendOTP($user);

            return $user;

        } catch (\Exception $e) {
            //return error message
            return response()->json([
                'errors'  => $e->getMessage(),
                'message' => 'User Registration Failed!',
            ], 409);

        }
    }

    public function resendOtp(Request $request){
        try{
            $validator = Validator::make($request->all(), [
                'user_id' => 'required|uuid',
            ]);
            if ($validator->fails()) {
                return response()->json($validator->errors(), 422);
            } else {
                $user_id = $request->input('user_id');
                $user = User::on('mysql::read')->findOrFail($user_id);
                $this->sendOtp($user);
            }
        }catch(ModelNotFoundException $me){
            return response()->json(['message'=>'User not found.'], 404);
        }catch(Exception $e){
            return response()->json(['message'=>$e->getMessage()], 422);
        }
    }

    public function sendOTP($user)
    {
        $otp = mt_rand(10000,99999);
        OtpVerify::on('mysql::write')->create([
            'user_id' => $user->id,
            'otp' => $otp,
            'expires_at' => Carbon::now()->addMinutes(env('OTP_VALIDITY'))
        ]);
        $message = "Hello! Your Xchange Verification Code is $otp. Code is valid for the next ".env('OTP_VALIDITY')."minutes.";
        // $this->sendSms($user->phone,$message);
        Mail::to($user->email)->send(new OtpMail($user->name, $otp));

        return "OTP successfully generated";
    }

    public function verifyOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'otp' => 'required|numeric',
        ]);


        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        } else {
            $otp = $request->input('otp');
            $userId = $request->input('user_id');

            $verifyOtp = OtpVerify::on('mysql::read')->where([
                'otp' => $otp,
                'user_id' => $userId
            ])->first();
            if(!empty($verifyOtp)){
                if(Carbon::now() >= $verifyOtp->expires_at){
                    return response()->json(['errors' => 'OTP is no longer valid'],403);
                }
                return $this->registerUsers($userId);
            } else {
                return response()->json(['errors' => 'OTP does not exist'],404);
            }
        }
    }

    public function registerUsers($userId)
    {
        try {
             $inviteeUser = InviteeUser::on('mysql::read')->where('id',$userId)->first();
             $user = '';

                 if(!empty($inviteeUser)){
                     $acc_no = '51'.substr(uniqid(mt_rand(), true), 0, 8);

                     $user                 = new User();
                     $user->name           = $inviteeUser->name;
                     $user->phone   = $inviteeUser->phone;
                     $user->email   = $inviteeUser->email;
                     $user->password       = app('hash')->make($inviteeUser->password);
                     $user->save();

                     $wallet = $user->wallet()->save(new Wallet());

                     $accNumber = AccountNumber::on('mysql::write')->create([
                         'account_number'=>$acc_no,
                         'account_name' => 'Wallet ID',
                         'wallet_id'=>$wallet->id,
                     ]);

                     $credentials = $inviteeUser->only(['phone', 'password']);

                     CustomerValidation::on('mysql::write')->create([
                         'user_id' => $user->id
                     ]);
                     if (!$token = Auth::attempt($credentials)) {
                         return response()->json(['message' => 'Unable to create token, kindly login'], 401);
                     }
                     Mail::to($inviteeUser->email)
                         ->send(new OnboardingMail($inviteeUser));

                     return response()->json([
                         'status'  => 0,
                         'message' => 'Account created Successfully',
                         'user'    => [
                             'id'           => $user->id,
                             "name"         => $user->name,
                             "phone" => $user->phone,
                         ],

                         'account_number'=>$user->wallet->account_numbers,
                         'walletBalance' => $user->wallet->balance,
                         'data'           => $this->respondWithToken($token)
                     ], 201);
                 } else {
                     return response()->json([
                         'message' => 'Identity could not be verified.',
                     ], 403);
                 }



        } catch (\Exception $e) {
            //return error message
            return response()->json([
                'errors'  => $e->getMessage(),
                'message' => 'User Registration Failed!',
            ], 409);

        }
    }

    public function login(Request $request)
    {
        try{
            $validator = Validator::make($request->all(), [
                'phone' => 'string',
                'email' => 'string|email',
                'password' => 'nullable|string|min:6',
            ]);

            // return $request;
            if ($validator->fails()) {
                return response()->json($validator->errors(), 422);
            }
            $credentials = $request->only(['phone', 'password', 'email']);

            if (!$token = Auth::attempt($credentials)) {
                return response()->json([
                    'status'    =>  -1,
                    'message'   => 'Invalid credentials'
                ], 401);
            }

            if ($this->isAdminShutdownStatus()) {
                return response()->json(['message' => 'your account has been suspended by the admin. contact customer\'s support'], 405);
            }

            $user = User::on('mysql::read')->where('phone', $request->phone)->first();
            if(empty($user->customer_verification)){
                CustomerValidation::on('mysql::write')->create([
                    'user_id' => $user->id
                ]);
            }

            if(!empty($user) && $user->status !== 1 && env('APP_ENV') !== 'local'){

                return response()->json(['status' =>  -1,'message' => 'Your Account has been deactivated, please contact Admin'],401);
            }

            $this->saveUserActivity(ActivityType::LOGIN, '', $user->id);


           UserActivityJob::dispatch($user->id, 'Login');
            $accs = array();
            $wallet = Wallet::where('user_id', $user->id)->first();
            foreach($wallet->account_numbers as $acc){
                $temp = array();
                $temp['account_name'] = $acc->account_name;
                $temp['account_number'] = $acc->account_number;

                array_push($accs, $temp);
            }

            return response()->json([
                'status'   =>   0,
                'message'  =>   'Successful',
                'user'     =>   [
                    'id'            => $user->id,
                    "name"    => $user->name,
                    "email"         => $user->email,
                    "phone"         => $user->phone,
                    "bvn"       => $user->bvn,
                    "image"     => $user->image,
                    "dob"       => $user->dob,
                    "sex"       => $user->sex,
                    "status"       => $user->status,
                    // "accounts"       => $accs,
                    "account_type" => $user->accounttype? $user->accounttype->name : null,
                    'withdrawal_limit' => $user->withdrawal_limit,
                    'shutdown_level' => $user->shutdown_level = 0? 'NO' : 'YES',
                    'transaction_pin_set' => $user->transaction_pin ? true : false,
                ],


                'walletBalance'   =>   $user->wallet->balance,
                'access_token' => $token,
                "expires" => auth()->factory()->getTTL() * 60 * 2,
            ]);

        }catch(Exception $e){
            return response()->json(['message'=>$e->getMessage()], 422);
        }
    }


    //find current user
    public function getAuthUser(Request $request)
    {
        return response()->json(auth()->user());
    }
    //show all users
    public function show(User $user)
    {
        return response()->json($user, 200);
    }

    public function logout()
    {
        auth()->logout();
        return response()->json(['message' => 'Successfully logged out'], 200);
    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        return $this->createNewToken(auth()->refresh());
    }

    public function Users()
    {
        try {

            $data = User::orderBy('id', 'desc')->get();
            $message = 'data successfully fetched';

            return $this->sendResponse($data,$message);
        }catch (ModelNotFoundException $e) {
            return response()->json(['message' => $e->getMessage()],404);
        } catch(\Exception $e) {
            return response()->json(['message' => $e->getMessage()],500);
        }
    }


    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return array
     */
    protected function respondWithToken($token)
    {


        return array('access_token'=>$token, 'expires_in'=>auth()->factory()->getTTL() * 60 *2,);
    }

    public function AccountVerification(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email|max:100',
            'verified_otp' => 'required|string|min:4',

        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }
        $user = User::on('mysql::write')->where('email', $request->email)->first();
        if (!$user) {
            return response()->json([
                "status" => false,
                "message" => "User account does not exist"
            ], 404);
        } else {
            if ($user->verified_otp == $request->verified_otp) {
                $user->update(['verified' => 1, 'verified_otp' => Null]);
                return response()->json([
                    "status" => true,
                    "message" => "Your Account has been successfully created and verified"
                ]);
            } else {
                // access token provided does not match generated token
                return response()->json([
                    "status" => false,
                    "message" => "Invalid otp provided"
                ]);
            }
        }
    }

    public function verifyBVN(Request $request){
        try{
            $validator = Validator::make($request->all(), [
                'bvn'=>'required|numeric|unique:users',
            ]);

            if ($validator->fails()) {
                return response()->json($validator->errors(), 400);
            }

            if($request->filled('bvn')){
                $bvn = $request->input('bvn');
                $response = Http::withHeaders([
                    'Authorization' => 'Bearer '.env('VFD_KEY')
                ])->get(env('VFD_URL')."client?bvn=$bvn&wallet-credentials=".env('VFD_WALLET_ID'));

                if($response->getStatusCode() !== 200){
                    return response()->json(['message'=>'Invalid BVN or Could Not Reach BVN Service', 'valid_bvn'=>false], 403);
                }

                $user = User::on('mysql::write')->find(Auth::id());
                $name = $response['data']['firstName'] . ' ' . $response['data']['middleName'] . ' ' .$response['data']['lastName'];
                $user->update([
                    'bvn'=>$bvn,
                    'name'=>$name,
                    'dob'=>$response['data']['dateOfBirth'],
                ]);

                return response()->json(['message'=>'BVN successfully verified', 'valid_bvn'=>true]);
            }
        }catch(Exception $e){
            Http::post(env('VFD_HOOK_URL'),[
                'text' => $e->getMessage(),
                'username' => 'UserController - Verify BVN method (api.transave.com.ng) ',
                'icon_emoji' => ':ghost:',
                'channel' => env('SLACK_CHANNEL')
            ]);
            return response()->json(['message'=>$e->getMessage()], 420);
        }
    }

    public function update(Request $request, User $user)
    {
        $user = Auth::user();
        Http::post(env('VFD_HOOK_URL'),[
            'text' => "$user->name with phone $user->phone and id $user->id is trying to update profile",
            'username' => 'UserController - Profile',
            'icon_emoji' => ':boom:',
            'channel' => env('SLACK_CHANNEL')
        ]);

        $validator = Validator::make($request->all(),[
            'email'         => 'string|unique:users',
            'phone' => 'string|unique:users',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }
        $input = array();

        if ($request->filled('dob')) {
            $input['dob'] = $request->dob;
        }
        if ($request->filled('sex')) {
            $input['sex'] = $request->sex;
        }

        if ($request->filled('password')) {
            $request->merge([
                'password' => bcrypt($request->input('password'))
            ]);
        }
        /* if($request->filled('pin')){
            $input['transaction_pin'] = Hash::make($input['pin']);
        } */
//        return "success";

        if($request->hasFile('profile_image')){

            $file = $request->file('profile_image');
            $disk = 's3';
            $ext = $file->getClientOriginalExtension();
            $path = 'cover-image'.time().'.'.$ext;

            $storage = Storage::disk($disk)->putFileAs('transave/profile',$file,$path);
            // $input['profile_image'] = $storage;
            $exists = Storage::disk('s3')->get($storage);
            $store = '';
            if($exists){
                $store = Storage::disk('s3')->url($storage);
            }

            //return $store;
            $input['image'] = $store;

        }

        if ($this->ownsRecord($user->id)) {

            $user->update($input);

            $this->bankRegistrationHook->updateUserInfo($user);
            $response = [
                'status'   =>   1,
                'message'  =>   'Account updated succesfully',
//                'walletBalance'   =>   $user->wallet->balance,
//                'loanBalance'   =>   $user->loanbalance->balance,
//                'referral_link' => $user->referral_code,
            ];

            $accs = array();
            $wallet = Wallet::where('user_id', $user->id)->first();
            foreach($wallet->account_numbers as $acc){
                $temp = array();
                $temp['account_name'] = $acc->account_name;
                $temp['account_number'] = $acc->account_number;

                array_push($accs, $temp);
            }

            return response()->json([
                'status'   =>   1 ,
                'message'  =>   'Account updated succesfully',
                'user'     =>   [
                    'id'            => $user->id,
                    "name"    => $user->name,
                    "email"         => $user->email,
                    "phone"         => $user->phone,
                    "bvn"       => $user->bvn,
                    "image"     => $user->image,
                    "dob"       => $user->dob,
                    "sex"       => $user->sex,
                    "status"       => $user->status,
                    "accounts"       => $accs,
                    "account_type" => $user->accounttype? $user->accounttype->name : null,
                    'withdrawal_limit' => $user->withdrawal_limit,
                    'shutdown_level' => $user->shutdown_level = 0? 'NO' : 'YES',
                    'transaction_pin_set' => $user->transaction_pin ? true : false,
                ],

                'referral_link'=> $user->referral_code->referral_link,
                'walletBalance'   =>   $user->wallet->balance,
                'LoanBalance' => $user->loanbalance ? $user->loanbalance->balance : 0,
                //'access_token' => $token,
                //"expires" => auth()->factory()->getTTL() * 60 * 2,
            ]);
        }
        return response()->json(['message' => 'you are not the owner of this account']);
    }



    public function Notification()
    {
        return response()->json(\App\Models\Notification::orderBy('id', 'desc')->get());
    }

    public function Admin()
    {
        return response()->json(\App\Models\Admin::orderBy('id', 'desc')->get());
    }

    public function FAQ()
    {
        return response()->json(\App\Models\FAQ::orderBy('id', 'desc')->get());
    }


    public function shutdown($id, $value = ShutdownLevel::USER_SHUTDOWN)
    {
        $user = User::on('mysql::read')->find($id);
        $user->update([
            'shutdown_level' => $value
        ]);
        return $this->sendResponse(User::find($id), 'account shutdown state change successful');
    }

    public function action($id, $type = AccountRequestAction::ACCEPTED)
    {
        $account = AccountRequest::on('mysql::read')->find($id);
        $account->update([
            'status' => $type,
        ]);
        return $this->sendResponse(AccountRequest::find($id), 'account request acted upon successfully');
    }


    public function customers()
    {
        $filter = request()->query('filter');
        $state = request()->query('state');
        $builder = CustomerValidation::on('mysql::read')->select('users.id', 'customer_validations.authorized_stat', 'customer_validations.support_id', 'users.name', 'users.email', 'users.phone', 'users.image', 'users.dob', 'users.sex', 'users.withdrawal_limit', 'users.shutdown_level', 'account_types.name as account_type', 'customer_validations.updated_at AS verified_at', 'users.created_at', 'users.updated_at')
            ->join('users', 'customer_validations.user_id', '=', 'users.id')->join('account_types', 'users.account_type_id', 'account_types.id');

        if ($filter || $filter == '0') {
            $builder = $builder->where('customer_validations.authorized_stat', $filter);
        }

        if ($state || $state == '0') {
            $builder = $builder->where('users.shutdown_level', $state);
        }

        if (request()->query('search')) {
            $search = request()->query('search');
            $resources = $builder->where(function ($query) use ($search) {
                $query->where('users.name', 'like', '%'.$search.'%')
                    ->orWhere('account_types.name', 'like', '%'.$search.'%')
                    ->orWhere('users.phone', 'like', '%'.$search.'%')
                    ->orWhere('users.email', 'like', '%'.$search.'%');
            })->orderBy('customer_validations.created_at', 'desc')->paginate(10);
        }elseif (request()->query('start') && !request()->query('end')) {
            $startDate = Carbon::parse(request()->query('start'));
            $resources = $builder->where('customer_validations.created_at', '>=', $startDate)
                ->orderBy('customer_validations.created_at', 'desc')->paginate(10);
        }elseif(!request()->query('start') && request()->query('end')) {
            $endDate = Carbon::parse(request()->query('end'));
            $resources = $builder->where('customer_validations.created_at', '<=', $endDate)
                ->orderBy('customer_validations.created_at', 'desc')->paginate(10);
        }elseif (request()->query('start') && request()->query('end')) {
            $startDate = Carbon::parse(request()->query('start'));
            $endDate = Carbon::parse(request()->query('end'));
            $resources = $builder->where('customer_validations.created_at', '>=', $startDate)
                ->where('customer_validations.created_at', '<=', $endDate)
                ->orderBy('customer_validations.created_at', 'desc')->paginate(10);
        }else {
            $resources = $builder->orderBy('customer_validations.created_at', 'desc')->paginate(10);
        }

        return $this->sendResponse($resources, 'all customer validations requests fetched successfully');
    }

    public function verification(Request $request, $id)
    {

        $customer = CustomerValidation::where('id', $id)->orWhere('user_id', $id)->first();
        $this->validate($request, [
            'state' => 'required|integer|min:0|max:1',
        ]);
        $state = $request->get('state');
        $updated = $customer->update([
            'authorized_stat' => $state,
        ]);
        $user = $customer->user;

        $user->update([
            'account_type_id' => $state == 0? 1 : 2,
        ]);

        if ($updated)
            return $this->sendResponse($this->customers(), 'account verification state changed successfully');

        return $this->sendError('account validation failed');
    }



    public function activate($id)
    {
        $user = User::on('mysql::read')->find($id);
        switch ($user->shutdown_level) {
            case 0:
                return $this->sendResponse(null, 'your account is active already');
            case 1: {
                $user->shutdown_level = 0;
                $user->save();
                return $this->sendResponse($user, 'your account have been reactivated successfully');
            }
            case 2:
                return $this->sendError('your account is suspended. contact admin to unblock');
            default:
                return $this->sendResponse(null, 'unable to process request');
        }
    }
}

