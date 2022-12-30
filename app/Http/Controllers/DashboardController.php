<?php

namespace App\Http\Controllers;

use App\Enums\ActivityType;
use App\Models\AirtimeTransaction;
use App\Models\BinanceTransaction;
use App\Models\BinanceWallet;
use App\Models\BitcoinTransaction;
use App\Models\BitcoinWalletPass;
use App\Models\Category;
use App\Models\DataTransaction;
use App\Models\DogecoinTransaction;
use App\Models\DogeCoinWalletAddress;
use App\Models\EtherumWalletAdress;
use App\Models\EthTransaction;
use App\Models\LGA;
use App\Models\LitecoinTransaction;
use App\Models\LitecoinWalletAddress;
use App\Models\Logistic;
use App\Models\Order;
use App\Models\payment as ModelsPayment;
use App\Models\PolygonTransaction;
use App\Models\PolygonWalletAddress;
use App\Models\PowerTransaction;
use App\Models\Rider;
use App\Models\Shop;
use App\Models\State;
use App\Models\TVTransaction;
use App\Models\User;
use App\Models\WalletTransaction;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Faker\Provider\ar_EG\Payment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    public function Index(Request $request)
    {
        $new_user_regs_this_month=User::where('account_type_id', 1)->whereYear('created_at', date('Y'))->where('created_at', '>=', Carbon::now()->startOfMonth())->get()->count();
        $users = User::latest()->take(10)->get();
        $order_this_month = AirtimeTransaction::all()->count();
        $service_this_month = DataTransaction::all()->count();
        $products_this_month = TVTransaction::all()->count();
        $ordercomplaint_this_month = PowerTransaction::all()->count();

        $bit = BitcoinTransaction::all()->count();
        $eth = EthTransaction::all()->count();
        $lit = LitecoinTransaction::all()->count();
        $pol = PolygonTransaction::all()->count();
        $bnb = BinanceTransaction::all()->count();
        $dog = DogecoinTransaction::all()->count();
        $trans = WalletTransaction::latest()->take(10)->get();
        $userscnt = User::orderBy('created_at', 'desc')->get();


        return view('cms.index')->with([

            'new_user_regs_this_month' => $new_user_regs_this_month,
            'products_this_month' => $products_this_month,
            'order_this_month' => $order_this_month,
            'users' => $users,
            'bit' => $bit,
            'eth' => $eth,
            'lit' => $lit,
            'pol' => $pol,
            'dog' => $dog,
            'bnb' => $bnb,
            'trans' => $trans,
            'userscnt' => $userscnt,
            'service_this_month' => $service_this_month,
            'ordercomplaint_this_month' => $ordercomplaint_this_month
        ]);
    }

    public function Profile ()
    {

        return view('dashboard.profile');
    }

    public function ProfileUpdate(Request $request, $id)
    {

        $user = Logistic::find($id);
        $validatedData = $request->validate([
            'email' => ['nullable', 'string', 'max:255'],
            'name' => ['nullable', 'string', 'max:255'],
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);


        if($request->hasFile('image')){

            $file = $request->file('image');
            $disk = 's3';
            $ext = $file->getClientOriginalExtension();
            $path = 'admin'.time().'.'.$ext;


            $storage = Storage::disk($disk)->putFileAs('admin/img/',$file,$path);
            $db_location = 'https://d3t7szus8c85is.cloudfront.net/admin/img/' . $path;

            // $input['profile_image'] = $storage;
            $exists = Storage::disk('s3')->get($storage);
            $store = '';
            if($exists){
                $store = Storage::disk('s3')->url($storage);
            }

            //return $store;
            $user['image']  = $db_location;

        }

        $user->name = $request->name;
        $user->email = $request->email;
        $user->phone = $request->phone;
        $user->address = $request->address;

        if ($user->save()) {

            Session::flash('success', 'Profile successfully updated!');
            return redirect()->back();
        }

       else
       { Session::flash('error', 'Profile could not be updated! Try again');
        return redirect()->back();
       }
    }

    public function AdminupdatePassword(Request $request, $id)
    {
        $user = Logistic::find($id);
        $password = $user->password;
        $validatedData = $request->validate([
            'new_password' => ['required', 'string', 'min:6'],
        ]);


        // $hashedPassword = Auth::guard('marketer')->password;
        $hashedPassword = Auth::user()->password;

        if (Hash::check($request->old_password, $password)) {
            $user->password = Hash::make($request->new_password);
            $user->save();

            Session::flash('success', 'Password successfully updated!');
            return redirect()->back();
        } else {

            Session::flash('error', 'Password did not match! Try again.');
            return redirect()->back();
        }
    }

    public function AllShops()
    {

        $contacts = Shop::orderBy('created_at', 'desc')->get();
        $pasengercnt = Shop::count();
        $states = State::all();
        $users = User::orderBy('created_at', 'desc')->get();
        $cats = Category::all();
        $lga = LGA::all();

        return view('shop.all_shops', compact(['pasengercnt', 'contacts', 'states', 'users', 'cats', 'lga']));

    }

    public function TripHistory()
    {

        $contacts = Order::orderBy('created_at', 'desc')->get();
        $pasengercnt = Order::where('delivery_status', 1)->where('delivery_status', 2)->count();
        $users = Rider::all();


        return view('dashboard.trip_history', compact(['pasengercnt', 'contacts', 'users']));

    }


    public function AssignRider (Request $request, $id)
    {
        $data = array(
            'rider_id'   => $request->rider_id,
            'rider_status'   => 1,
        );


        $property = Order::find($id);

        $property->update($data);

        Session::flash('success', 'Driver Assigned successfully.');

        return redirect()->back();
    }

    public function RiderProfUpdate(Request $request, $id)
    {

        $user = Rider::find($id);
        $validatedData = $request->validate([
            'email' => ['nullable', 'string', 'max:255'],
            'fullname' => ['nullable', 'string', 'max:255'],
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);


        if($request->hasFile('image')){

            $file = $request->file('image');
            $disk = 's3';
            $ext = $file->getClientOriginalExtension();
            $path = 'logistic'.time().'.'.$ext;


            $storage = Storage::disk($disk)->putFileAs('logistics/',$file,$path);
            $db_location = 'https://d3t7szus8c85is.cloudfront.net/logistics/' . $path;

            // $input['profile_image'] = $storage;
            $exists = Storage::disk('s3')->get($storage);
            $store = '';
            if($exists){
                $store = Storage::disk('s3')->url($storage);
            }

            //return $store;
            $user['image']  = $db_location;

        }

        $user->fullname = $request->fullname;
        $user->email = $request->email;
        $user->phone = $request->phone;
        $user->address = $request->address;
        $user->plate_numb = $request->plate_numb;
        $user->driver_license = $request->driver_license;


        if ($user->save()) {

            Session::flash('success', 'Profile successfully updated!');
            return redirect()->back();
        }

       else
       { Session::flash('error', 'Profile could not be updated! Try again');
        return redirect()->back();
       }
    }

    public function RiderEdit($id)

    {
        $product = Rider::find($id);

        return view('dashboard.edit_rider', compact(['product']));
    }

    public function Userdetails($id)

    {
        $product = Rider::find($id);

        return view('dashboard.rider', compact(['product']));
    }
}
