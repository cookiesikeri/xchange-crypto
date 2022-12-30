<?php

namespace App\Http\Controllers;

use App\Enums\ActivityType;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Traits\ManagesUsers;
use Carbon\Carbon;
use App\Mail\OnboardingMail;
use App\Models\AccountNumber;
use App\Models\Coupon;
use App\Models\Follower;
use App\Models\Referral;
use App\Models\ReferralCode;
use App\Models\Transaction;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;



class ConsumerController extends Controller
{
    use ManagesUsers;

    public function __construct()
    {
        $this->middleware('auth:admin');
    }


    public function registerUser(Request $request, $referral_code=null)
    {

        $validator = Validator::make($request->all(), [
            'name'         => 'required|string|unique|between:4,100',
            'email'         => 'required|email|unique',
            'phone' => 'required|string',
            'password'     => 'required|string|min:6'
        ]);

        if($validator->fails()) {
            Session::flash('fail', 'Account could not be created!');
            return redirect()->back()->withErrors($validator)->withInput();
        }
        $acc_no = '51'.substr(uniqid(mt_rand(), true), 0, 8);
            $user             = new User();
            $user->name   = $request->input('name');
            $user->phone   = $request->input('phone');
            $user->email   = $request->input('email');
            $user->sex   = $request->input('sex');
            $user->dob   = $request->input('dob');
            $user->password   = Hash::make($request->password);

            if($request->hasFile('picture')){

                $file = $request->file('picture');
                $disk = 's3';
                $ext = $file->getClientOriginalExtension();
                $path = 'profileImage'.time().'.'.$ext;


                $storage = Storage::disk($disk)->putFileAs('profileImage/',$file,$path);
                $db_location = 'https://d3t7szus8c85is.cloudfront.net/profileImage/' . $path;

                // $input['profile_image'] = $storage;
                $exists = Storage::disk('s3')->get($storage);
                $store = '';
                if($exists){
                    $store = Storage::disk('s3')->url($storage);
                }

                //return $store;
                $user['picture']  = $db_location;

            }

            $user->save();

            $wallet = $user->wallet()->save(new Wallet());
            $accNumber = AccountNumber::on('mysql::write')->create([
                'account_number'=>$acc_no,
                'account_name' => 'Wallet ID',
                'wallet_id'=>$wallet->id,
            ]);


            $this->saveUserActivity(ActivityType::REGISTER, '', $user->id);

            Mail::to($request->email)->send(new \App\Mail\OnboardingMail($user));

            Session::flash('success', 'User account successfully created for: ' . $user['name']);

        return redirect()->back();

    }


    public function TodayUsersreg(Request $request)
    {
        $contacts = User::where('account_type_id', 1)->where('created_at', '>=', date('Y-m-d') . ' 00:00:00')->get();
        $pasengercnt = User::where('account_type_id', 1)->where('created_at', '>=', date('Y-m-d') . ' 00:00:00')->count();

        return view('cms.consumer.today_consumers', compact(['pasengercnt', 'contacts']));

    }

    public function updateConsumer (Request $request, $id)
    {
        $product = User::where('id', $id)->first();


        $product->name = $request->name;
        $product->phone = $request->phone;
        $product->email = $request->email;
        $product->sex = $request->sex;
        $product->dob = $request->dob;
        $product->password   = Hash::make($request->password);

        if($request->hasFile('picture')){

            $file = $request->file('picture');
            $disk = 's3';
            $ext = $file->getClientOriginalExtension();
            $path = 'profileImage'.time().'.'.$ext;


            $storage = Storage::disk($disk)->putFileAs('profileImage/',$file,$path);
            $db_location = 'https://d3t7szus8c85is.cloudfront.net/profileImage/' . $path;

            // $input['profile_image'] = $storage;
            $exists = Storage::disk('s3')->get($storage);
            $store = '';
            if($exists){
                $store = Storage::disk('s3')->url($storage);
            }

            //return $store;
            $product['picture']  = $db_location;

        }

        $product->save();

        Session::flash('success', 'User updated successfully.');

        return redirect()->back();
    }

    public function Consumers(Request $request)
    {
        $contacts = User::where('account_type_id', 1)->orderBy('created_at', 'desc')->get();
        $pasengercnt = User::where('account_type_id', 1)->count();

        return view('cms.consumer.consumers', compact(['pasengercnt', 'contacts']));


    }

    public function ThisMonthUsers(Request $request)
    {
        $contacts=User::where('account_type_id', 1)->whereYear('created_at', date('Y'))->where('created_at', '>=', Carbon::now()->startOfMonth())->orderBy('created_at', 'desc')->get();
        $pasengercnt=User::where('account_type_id', 1)->whereYear('created_at', date('Y'))->where('created_at', '>=', Carbon::now()->startOfMonth())->count();

        return view('cms.consumer.ThisMonthUsers', compact(['pasengercnt', 'contacts']));


    }

    public function DeletedUsers()
    {

        $contacts = User::where('is_deleted',  1)->orderBy('created_at', 'desc')->get();
        $pasengercnt = User::where('is_deleted', 1)->count();


        return view('cms.consumer.deleted_consumers', compact(['pasengercnt', 'contacts']));

    }


    public function AddUser()
    {

        return view('cms.consumer.add_user');
    }

    public function deleteConsumer ($id) {
        // User::destroy($id);

        $data =DB::table('users')
        ->leftJoin('wallets','users.id', '=','wallets.user_id')
        ->where('users.id', $id)
        ->leftJoin('account_numbers','users.id', '=','account_numbers.user_id')
        ->where('users.id', $id);
        DB::table('wallets')->where('user_id', $id)->delete();
        $data->delete();
        DB::table('account_numbers')->where('user_id', $id)->delete();
        $data->delete();

        Session::flash('success', 'User Deleted successfully!');
        return redirect()->back();
      }


      public function updatewalletShow ($id) {
        $post = Wallet::findorfail($id);
        return $post;
    }


    public function WalletManager()
    {

        $contacts = Wallet::orderBy('created_at', 'desc')->get();
        $pasengercnt = Wallet::all()->count();


        return view('cms.wallet', compact(['pasengercnt', 'contacts']));
    }
    public function Accountnumbers()
    {

        $contacts = AccountNumber::orderBy('created_at', 'desc')->get();
        $pasengercnt = AccountNumber::all()->count();


        return view('cms.consumer.Accountnumbers', compact(['pasengercnt', 'contacts']));
    }


    public function WalletEdit($id)

    {
        $product = Wallet::find($id);

        return view('cms.consumer.edit_wallet',compact('product'));
    }

    public function editConsumer($id)

    {
        $product = User::find($id);

        return view('cms.consumer.edit_consumer',compact('product'));
    }


    public function WalletUpdate (Request $request, $id)
    {
        $product = Wallet::where('id', $id)->first();

        $product->balance = $request->balance;

        $product->save();

        Session::flash('success', 'User Wallet updated successfully.');

        return redirect()->back();
    }

    public function BanneUser($id)
    {
        $product = User::where('id', $id)->first();

        $product->is_banned = 1;
        $product->is_active = 0;

        $product->save();

        Session::flash('success', 'User Banned successfully.');

        return redirect()->back();
    }

    public function UnBanneUser($id)
    {
        $product = User::where('id', $id)->first();

        $product->is_banned = 0;
        $product->is_active = 0;

        $product->save();

        Session::flash('success', 'User UnBanned successfully.');

        return redirect()->back();
    }





}
