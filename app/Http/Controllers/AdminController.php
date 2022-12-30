<?php

namespace App\Http\Controllers;



use App\Models\User;
use App\Models\Admin;
use App\Models\Role;
use App\Models\Permission;
use App\Models\ContactMessage;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Course;
use Illuminate\Support\Facades\Validator;
use Image;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use App\Mail\SendEmail;
use App\Models\Accountant;
use App\Models\AccountType;
use App\Models\ActivityType;
use App\Models\AirtimeTransaction;
use App\Models\Bank;
use App\Models\BinanceTransaction;
use App\Models\BinanceWallet;
use App\Models\BitcoinPrivateKey;
use App\Models\BitcoinTransaction;
use App\Models\BitcoinWalletPass;
use App\Models\BitconWallet;
use App\Models\Brand;
use App\Models\Cart;
use App\Models\Category;
use App\Models\ClothSize;
use App\Models\Color;
use App\Models\Country;
use App\Models\Coupon;
use App\Models\CustomerCare;
use App\Models\DataBundle;
use App\Models\DataEntry;
use App\Models\DataTransaction;
use App\Models\Devteam;
use App\Models\DogecoinPrivateKey;
use App\Models\DogecoinTransaction;
use App\Models\DogeCoinWallet;
use App\Models\DogeCoinWalletAddress;
use App\Models\EtherumPrivateKey;
use App\Models\EtherumWallet;
use App\Models\EtherumWalletAdress;
use App\Models\EthTransaction;
use App\Models\FAQs;
use App\Models\GetUser;
use App\Models\GiftCard;
use App\Models\GiftCardActivity;
use App\Models\GiftCardCustomer;
use App\Models\Interest;
use App\Models\LitecoinPrivateKey;
use App\Models\LitecoinTransaction;
use App\Models\LitecoinWallet;
use App\Models\LitecoinWalletAddress;
use App\Models\Logistic;
use App\Models\Models\OtpVerify;
use App\Models\Order;
use App\Models\OrderComplaint;
use App\Models\OrderContent;
use App\Models\Pegg;
use App\Models\PolygonPrivateKey;
use App\Models\PolygonTransaction;
use App\Models\PolygonWallet;
use App\Models\PolygonWalletAddress;
use App\Models\PowerTransaction;
use App\Models\Product;
use App\Models\ProductAttribute;
use App\Models\ProductPicture;
use App\Models\ProductVideo;
use App\Models\PromotionalVideo;
use App\Models\PromotionalVideoProduct;
use App\Models\SecurityQuestion;
use App\Models\Service;
use App\Models\ServiceActivity;
use App\Models\ServiceAvailability;
use App\Models\ServiceCategory;
use App\Models\ServicePackage;
use App\Models\ServicePakage;
use App\Models\ServicePlan;
use App\Models\ServiceType;
use App\Models\ShoeSize;
use App\Models\Shop;
use App\Models\State;
use App\Models\SubCategory;
use App\Models\Transaction;
use App\Models\TVBundle;
use App\Models\TVTransaction;
use App\Models\UserActivity;
use App\Models\UserOTP;
use App\Models\UserSecretQAndA;
use App\Models\VideoTag;
use App\Models\VirtualAccount;
use App\Models\VirtualCard;
use App\Models\VirtualCardRequest;
use App\Models\Wallet;
use Carbon\Carbon;
use App\Traits\ManagesUploads;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;




class AdminController extends Controller
{
        public function __construct()
    {
        $this->middleware('auth:admin');
    }


        public function adminIndex(Request $request)
    {
        $year=date('Y');
        if($request->has('year')){
            $year=$request->year;
        }
        //this month new registrations

        $new_user_regs_this_month=User::where('account_type_id', 1)->whereYear('created_at', date('Y'))->where('created_at', '>=', Carbon::now()->startOfMonth())->get()->count();
        $order_this_month=Order::whereYear('created_at', date('Y'))->where('created_at', '>=', Carbon::now()->startOfMonth())->get()->count();
        $service_this_month=Service::whereYear('created_at', date('Y'))->where('created_at', '>=', Carbon::now()->startOfMonth())->get()->count();
        $ordercomplaint_this_month=OrderComplaint::whereYear('created_at', date('Y'))->where('created_at', '>=', Carbon::now()->startOfMonth())->get()->count();
        $products_this_month=Product::whereYear('created_at', date('Y'))->where('created_at', '>=', Carbon::now()->startOfMonth())->get()->count();
        $coupon_this_month=Coupon::whereYear('created_at', date('Y'))->where('created_at', '>=', Carbon::now()->startOfMonth())->get()->count();
        $shop_this_month=Shop::whereYear('created_at', date('Y'))->where('created_at', '>=', Carbon::now()->startOfMonth())->get()->count();
        $trans_this_month=Transaction::whereYear('created_at', date('Y'))->where('created_at', '>=', Carbon::now()->startOfMonth())->get()->count();
        $prod_vid_this_month=ProductVideo::whereYear('created_at', date('Y'))->where('created_at', '>=', Carbon::now()->startOfMonth())->get()->count();
        $prod_pic_this_month=ProductPicture::whereYear('created_at', date('Y'))->where('created_at', '>=', Carbon::now()->startOfMonth())->get()->count();
        $retailer_this_month=User::where('account_type_id', 2)->whereYear('created_at', date('Y'))->where('created_at', '>=', Carbon::now()->startOfMonth())->get()->count();
        $manufact_this_month=User::where('account_type_id', 3)->whereYear('created_at', date('Y'))->where('created_at', '>=', Carbon::now()->startOfMonth())->get()->count();
        $orders = Order::latest()->take(10)->get();
        $products = Product::latest()->take(10)->get();
        $users = User::latest()->take(10)->get();
        $services = Service::latest()->take(10)->get();
        $contacts = Shop::latest()->take(10)->get();

        $record = Order::select(DB::raw("COUNT(*) as count"), DB::raw("DAYNAME(created_at) as day_name"), DB::raw("DAY(created_at) as day"))
        ->where('created_at', '>', Carbon::today()->subDay(6))
        ->groupBy('day_name','day')
        ->orderBy('day')
        ->get();

        $data = [];

        foreach($record as $row) {
            $data['label'][] = $row->day_name;
            $data['data'][] = (int) $row->count;
        }

        $data['chart_data'] = json_encode($data);

        return view('cms.index')->with([
            'data' => $data,
            'new_user_regs_this_month' => $new_user_regs_this_month,
            'orders' => $orders,
            'shop_this_month' => $shop_this_month,
            'products_this_month' => $products_this_month,
            'order_this_month' => $order_this_month,
            'users' => $users,
            'ordercomplaint_this_month' => $ordercomplaint_this_month,
            'products' => $products,
            'contacts' => $contacts,
            'manufact_this_month' => $manufact_this_month,
            'trans_this_month' => $trans_this_month,
            'services' => $services,
            'retailer_this_month' => $retailer_this_month,
            'prod_pic_this_month' => $prod_pic_this_month,
            'service_this_month' => $service_this_month,
            'coupon_this_month' => $coupon_this_month,
            'prod_vid_this_month' => $prod_vid_this_month
        ]);
    }

    public function AdminProfile (Request $request)
    {

        return view('cms.admin.profile');
    }

    public function AdminProfileUpdate(Request $request, $id)
    {

        $user = Admin::find($id);
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

        if ($user->save()) {

            Session::flash('success', 'Profile successfully changed!');
            return redirect()->back();
        }

       else
       { Session::flash('error', 'Profile could not be updated! Try again');
        return redirect()->back();
       }
    }


    public function AdminupdatePassword(Request $request, $id)
    {
        $user = Admin::find($id);
        $password = $user->password;
        $validatedData = $request->validate([
            'new_password' => ['required', 'string', 'min:6'],
        ]);

        $hashedPassword = Auth::user()->password;
        if (Hash::check($request->old_password, $password)) {
            $user->password = Hash::make($request->new_password);
            $user->save();

            Session::flash('success', 'Password successfully changed!');
            return redirect()->back();
        } else {

            Session::flash('error', 'Password could not be updated!! Try again.');
            return redirect()->back();
        }
    }


    public function system_config ()
    {

        return view('cms.settings.system_config');
    }

    public function ActivityTypes ()
    {
        $contacts = ActivityType::orderBy('created_at', 'desc')->get();

        return view('cms.settings.activity_types', compact('contacts'));
    }

    public function FAQ ()
    {
        $contacts = FAQs::orderBy('id', 'desc')->get();

        return view('cms.pages.faqs', compact('contacts'));
    }

    public function site_configuration()
    {

        return view('cms.settings.settings');
    }

    public function system_config_update(Request $request)
    {
        $data = array(
            'andriod_app_version'   => $request->andriod_app_version,
            'ios_app_version'   => $request->ios_app_version,
            'base_url' => $request->base_url,
            'backup_url' => $request->backup_url,
        );

        $validator = Validator::make($data, [
            'andriod_app_version'   => 'nullable|string|max:20',
            'ios_app_version' => 'nullable|string|max:100',
            'base_url'  => 'nullable|string',
            'backup_url'  => 'nullable|string',


        ]);

        if($validator->fails())
        {
            return redirect()->back()->withErrors($validator->errors())->withInput();
        }

        $site_config = \App\Models\SystemConfig::find(1);

        if($site_config)
        {
            Session::flash('success', 'Config details successfully updated.');
            $site_config->update($data);
        }
        else
        {
            Session::flash('success', 'Config details successfully created.');
            \App\Models\SystemConfig::create($data);
        }
        return redirect()->back();
    }

    public function site_configuration_update(Request $request)
    {
        $data = array(
            'hotline'   => $request->hotline,
            'hotline2'   => $request->hotline2,
            'whatsapp'   => $request->whatsapp,
            'site_name' => $request->site_name,
            'site_email'    => $request->site_email,
            'site_address'  => $request->site_address,
            'facebook'  => $request->facebook,
            'twitter'   => $request->twitter,
            'linkedin'    => $request->linkedin,
            'instagram' => $request->instagram,
            'youtube' => $request->youtube,

        );

        if($request->hasFile('logo'))
        {
            $image = $request->logo;
            $file = $request->file('logo');
            $name = $file->getClientOriginalName();

            $location = 'img/'. $name;

            Image::make($image)->resize(1169, 538, function ($constraint) {
                $constraint->aspectRatio();})->save($location);

            $data['logo'] = $location;

        }

        $validator = Validator::make($data, [
            'hotline'   => 'nullable|string|max:20',
            'hotline2'   => 'nullable|string|max:20',
            'site_name' => 'nullable|string|max:100',
            'site_email'    => 'nullable|string|max:100',
            'site_address'  => 'nullable|string',
            'facebook'  => 'nullable|string|max:100',
            'twitter'   => 'nullable|string|max:100',
            'linkedin'    => 'nullable|string|max:100',
            'instagram' => 'nullable|string|max:100',
            'youtube' => 'nullable|string|max:100',

        ]);

        if($validator->fails())
        {
            return redirect()->back()->withErrors($validator->errors())->withInput();
        }

        $site_config = \App\Models\SiteSetting::find(1);

        if($site_config)
        {
            Session::flash('success', 'Site details successfully updated.');
            $site_config->update($data);
        }
        else
        {
            Session::flash('success', 'Site details successfully created.');
            \App\Models\SiteSetting::create($data);
        }
        return redirect()->back();
    }



    public function PassengersOtp()
    {
         $contacts = OtpVerify::orderBy('id', 'desc')->get();

        return view('cms.security.otp', compact(['contacts']));
    }


    public function SecurityQuestion()
    {
         $contacts = UserSecretQAndA::orderBy('id', 'desc')->get();

        return view('cms.security.securityquestion', compact(['contacts']));
    }

    public function UserActivities()
    {
         $contacts = UserActivity::orderBy('created_at', 'desc')->get();

        return view('cms.activity', compact(['contacts']));
    }

    public function Aritime()
    {
         $contacts = AirtimeTransaction::orderBy('id', 'desc')->get();
         $pasengercnt = AirtimeTransaction::all()->count();

        return view('cms.bills.airtime_transaction', compact(['contacts', 'pasengercnt']));
    }

    public function DATa()
    {
         $contacts = DataTransaction::orderBy('date_created', 'desc')->get();
         $pasengercnt = DataTransaction::all()->count();

        return view('cms.bills.data_transaction', compact(['contacts', 'pasengercnt']));
    }

    public function TV()
    {
         $contacts = TVTransaction::orderBy('date_created', 'desc')->get();
         $pasengercnt = TVTransaction::all()->count();

        return view('cms.bills.tv_transaction', compact(['contacts', 'pasengercnt']));
    }
    public function Power()
    {
         $contacts = PowerTransaction::latest('id', 'desc')->get();
         $pasengercnt = PowerTransaction::all()->count();

        return view('cms.bills.power_transaction', compact(['contacts', 'pasengercnt']));
    }
    public function Tvplans()
    {
         $contacts = TVBundle::orderBy('id', 'desc')->get();
         $pasengercnt = TVBundle::all()->count();

        return view('cms.bills.Tvplans', compact(['contacts', 'pasengercnt']));
    }
    public function Powerplans()
    {
         $contacts = Service::where('service_type_id', 3)->orderBy('id', 'desc')->get();
         $pasengercnt = Service::where('service_type_id', 3)->count();

        return view('cms.bills.Powerplans', compact(['contacts', 'pasengercnt']));
    }
    public function BTCWallet()
    {
         $contacts = BitconWallet::orderBy('id', 'desc')->get();
         $pasengercnt = BitconWallet::all()->count();

        return view('cms.BTCWallet', compact(['contacts', 'pasengercnt']));
    }
    public function BNBWallet()
    {
         $contacts = BinanceWallet::orderBy('id', 'desc')->get();
         $pasengercnt = BinanceWallet::all()->count();

        return view('cms.BNBWallet', compact(['contacts', 'pasengercnt']));
    }

    public function ETHWallet()
    {
         $contacts = EtherumWalletAdress::orderBy('id', 'desc')->get();
         $pasengercnt = EtherumWalletAdress::all()->count();

        return view('cms.ETHWallet', compact(['contacts', 'pasengercnt']));
    }
    public function LTCWallet()
    {
         $contacts = LitecoinWalletAddress::orderBy('id', 'desc')->get();
         $pasengercnt = LitecoinWalletAddress::all()->count();

        return view('cms.LTCWallet', compact(['contacts', 'pasengercnt']));
    }
    public function POLWallet()
    {
         $contacts = PolygonWalletAddress::orderBy('id', 'desc')->get();
         $pasengercnt = PolygonWalletAddress::all()->count();

        return view('cms.POLWallet', compact(['contacts', 'pasengercnt']));
    }
    public function DogecoinWallet()
    {
         $contacts = DogeCoinWalletAddress::orderBy('id', 'desc')->get();
         $pasengercnt = DogeCoinWalletAddress::all()->count();

        return view('cms.DogecoinWallet', compact(['contacts', 'pasengercnt']));
    }
    public function dataPlans()
    {
         $contacts = DataBundle::orderBy('id', 'desc')->get();
         $pasengercnt = DataBundle::all()->count();

        return view('cms.bills.dataPlans', compact(['contacts', 'pasengercnt']));
    }

    public function BTCtransactions()
    {
         $contacts = BitcoinTransaction::orderBy('id', 'desc')->get();
         $pasengercnt = BitcoinTransaction::all()->count();

        return view('cms.BTCtransactions', compact(['contacts', 'pasengercnt']));
    }
    public function BNBtransactions()
    {
         $contacts = BinanceTransaction::orderBy('id', 'desc')->get();
         $pasengercnt = BinanceTransaction::all()->count();

        return view('cms.BNBtransactions', compact(['contacts', 'pasengercnt']));
    }
    public function ETHtransactions()
    {
         $contacts = EthTransaction::orderBy('id', 'desc')->get();
         $pasengercnt = EthTransaction::all()->count();

        return view('cms.ETHtransactions', compact(['contacts', 'pasengercnt']));
    }
    public function LTCtransactions()
    {
         $contacts = LitecoinTransaction::orderBy('id', 'desc')->get();
         $pasengercnt = LitecoinTransaction::all()->count();

        return view('cms.lthtransactions', compact(['contacts', 'pasengercnt']));
    }
    public function POLtransactions()
    {
         $contacts = PolygonTransaction::orderBy('id', 'desc')->get();
         $pasengercnt = PolygonTransaction::all()->count();

        return view('cms.POLtransactions', compact(['contacts', 'pasengercnt']));
    }
    public function Dogecointransactions()
    {
         $contacts = DogecoinTransaction::orderBy('id', 'desc')->get();
         $pasengercnt = DogecoinTransaction::all()->count();

        return view('cms.Dogecointransactions', compact(['contacts', 'pasengercnt']));
    }
    public function BTCmnemonic()
    {
         $contacts = BitcoinWalletPass::orderBy('id', 'desc')->get();
         $pasengercnt = BitcoinWalletPass::all()->count();

        return view('cms.BTCmnemonic', compact(['contacts', 'pasengercnt']));
    }
    public function BNBmnemonic()
    {
         $contacts = BinanceWallet::orderBy('id', 'desc')->get();
         $pasengercnt = BinanceWallet::all()->count();

        return view('cms.BNBmnemonic', compact(['contacts', 'pasengercnt']));
    }
    public function ETHmnemonic()
    {
         $contacts = EtherumWallet::orderBy('id', 'desc')->get();
         $pasengercnt = EtherumWallet::all()->count();

        return view('cms.ETHmnemonic', compact(['contacts', 'pasengercnt']));
    }
    public function LTCmnemonic()
    {
         $contacts = LitecoinWallet::orderBy('id', 'desc')->get();
         $pasengercnt = LitecoinWallet::all()->count();

        return view('cms.LTCmnemonic', compact(['contacts', 'pasengercnt']));
    }
    public function POLmnemonic()
    {
         $contacts = PolygonWallet::orderBy('id', 'desc')->get();
         $pasengercnt = PolygonWallet::all()->count();

        return view('cms.POLmnemonic', compact(['contacts', 'pasengercnt']));
    }
    public function Dogemnemonic()
    {
         $contacts = DogeCoinWallet::orderBy('id', 'desc')->get();
         $pasengercnt = DogeCoinWallet::all()->count();

        return view('cms.Dogemnemonic', compact(['contacts', 'pasengercnt']));
    }

    public function LTCKeys()
    {
         $contacts = LitecoinPrivateKey::orderBy('id', 'desc')->get();
         $pasengercnt = LitecoinPrivateKey::all()->count();

        return view('cms.LTCKeys', compact(['contacts', 'pasengercnt']));
    }
    public function BTCKeys()
    {
         $contacts = BitcoinPrivateKey::orderBy('id', 'desc')->get();
         $pasengercnt = BitcoinPrivateKey::all()->count();

        return view('cms.BTCKeys', compact(['contacts', 'pasengercnt']));
    }
    public function ETHKeys()
    {
         $contacts = EtherumPrivateKey::orderBy('id', 'desc')->get();
         $pasengercnt = EtherumPrivateKey::all()->count();

        return view('cms.ETHKeys', compact(['contacts', 'pasengercnt']));
    }
    public function POLKeys()
    {
         $contacts = PolygonPrivateKey::orderBy('id', 'desc')->get();
         $pasengercnt = PolygonPrivateKey::all()->count();

        return view('cms.POLKeys', compact(['contacts', 'pasengercnt']));
    }
    public function DogeKeys()
    {
         $contacts = DogecoinPrivateKey::orderBy('id', 'desc')->get();
         $pasengercnt = DogecoinPrivateKey::all()->count();

        return view('cms.DogeKeys', compact(['contacts', 'pasengercnt']));
    }

    public function virtualAccounts()
    {
         $contacts = VirtualAccount::orderBy('id', 'desc')->get();
         $pasengercnt = VirtualAccount::all()->count();

        return view('cms.virtualAccounts', compact(['contacts', 'pasengercnt']));
    }

    public function virtualRequests()
    {
         $contacts = VirtualCardRequest::orderBy('id', 'desc')->get();
         $pasengercnt = VirtualCardRequest::all()->count();

        return view('cms.virtualRequests', compact(['contacts', 'pasengercnt']));
    }

    public function virtualCards()
    {
         $contacts = VirtualCard::orderBy('id', 'desc')->get();
         $pasengercnt = VirtualCard::all()->count();

        return view('cms.virtualCards', compact(['contacts', 'pasengercnt']));
    }

    public function giftcardCustomer()
    {
         $contacts = GiftCardCustomer::orderBy('id', 'desc')->get();
         $pasengercnt = GiftCardCustomer::all()->count();

        return view('cms.giftcardCustomer', compact(['contacts', 'pasengercnt']));
    }
    public function Giftcards()
    {
         $contacts = GiftCard::orderBy('id', 'desc')->get();
         $pasengercnt = GiftCard::all()->count();

        return view('cms.Giftcards', compact(['contacts', 'pasengercnt']));
    }
    public function giftcardActivities()
    {
         $contacts = GiftCardActivity::orderBy('id', 'desc')->get();
         $pasengercnt = GiftCardActivity::all()->count();

        return view('cms.giftcardActivities', compact(['contacts', 'pasengercnt']));
    }


    public function AdminTermsofUse()
    {
        return view('cms.terms');
    }


    public function AdminPrivacy()
    {

        return view('cms.privacy', compact(['contacts', 'messages']));
    }


       public function destroy ($page, $id) {

        if ($page == 'virtualaccount') {

            $delete = VirtualAccount::find($id);

            // @unlink($delete->icon);

            if(!$delete)
            {
                abort(404);
            }

            $delete->delete();
            Session::flash('success', 'virtualaccount deleted successfully.');
            return redirect()->back();

        }

        if ($page == 'power') {

            $delete = PowerTransaction::find($id);

            if(!$delete)
            {
                abort(404);
            }

            $delete->delete();
            Session::flash('success', 'PowerTransaction deleted successfully.');
            return redirect()->back();

        }

        if ($page == 'tv') {

            $delete = TVTransaction::find($id);

            if(!$delete)
            {
                abort(404);
            }

            $delete->delete();
            Session::flash('success', 'TVTransaction deleted successfully.');
            return redirect()->back();

        }

        if ($page == 'data') {

            $delete = DataTransaction::find($id);


            if(!$delete)
            {
                abort(404);
            }

            $delete->delete();
            Session::flash('success', 'DataTransaction deleted successfully.');
            return redirect()->back();

        }

        if ($page == 'power') {

            $delete = PowerTransaction::find($id);


            if(!$delete)
            {
                abort(404);
            }

            $delete->delete();
            Session::flash('success', 'PowerTransaction deleted successfully.');
            return redirect()->back();

        }


        if ($page == 'virtualrequest') {

            $delete = VirtualCardRequest::find($id);


            if(!$delete)
            {
                abort(404);
            }

            $delete->delete();
            Session::flash('success', 'VirtualCardRequest deleted successfully.');
            return redirect()->back();

        }
        if ($page == 'btcwallets') {

            $delete = BitconWallet::find($id);


            if(!$delete)
            {
                abort(404);
            }

            $delete->delete();
            Session::flash('success', 'BitconWallet deleted successfully.');
            return redirect()->back();

        }

        if ($page == 'virtualcards') {

            $delete = VirtualCard::find($id);

            if(!$delete)
            {
                abort(404);
            }

            $delete->delete();
            Session::flash('success', 'VirtualCard deleted successfully.');
            return redirect()->back();

        }

        if ($page == 'powerplans') {

            $delete = Service::find($id);

            if(!$delete)
            {
                abort(404);
            }

            $delete->delete();
            Session::flash('success', 'Coupon deleted successfully.');
            return redirect()->back();

        }

        if ($page == 'tvplans') {

            $delete = TVBundle::find($id);


            if(!$delete)
            {
                abort(404);
            }

            $delete->delete();
            Session::flash('success', ' TVBundle deleted successfully.');
            return redirect()->back();

        }

        if ($page == 'dataplans') {

            $delete = DataBundle::find($id);


            if(!$delete)
            {
                abort(404);
            }

            $delete->delete();
            Session::flash('success', 'DataBundle deleted successfully.');
            return redirect()->back();

        }


        if ($page == 'giftcardcustomer') {

            $delete = GiftCardCustomer::find($id);


            if(!$delete)
            {
                abort(404);
            }

            $delete->delete();
            Session::flash('success', 'GiftCardCustomer  deleted successfully.');
            return redirect()->back();

        }

        if ($page == 'giftcards') {

            $delete = GiftCard::find($id);


            if(!$delete)
            {
                abort(404);
            }

            $delete->delete();
            Session::flash('success', 'GiftCard  deleted successfully.');
            return redirect()->back();

        }
        if ($page == 'giftcardactivities') {

            $delete = GiftCardActivity::find($id);


            if(!$delete)
            {
                abort(404);
            }

            $delete->delete();
            Session::flash('success', 'GiftCardActivity  deleted successfully.');
            return redirect()->back();

        }

        if ($page == 'users') {

            $delete = User::find($id);


            if(!$delete)
            {
                abort(404);
            }

            $delete->delete();
            Session::flash('success', 'User deleted successfully.');
            return redirect()->back();

        }

        if ($page == 'btcTransactions') {

            $delete = BitcoinTransaction::find($id);

            if(!$delete)
            {
                abort(404);
            }

            $delete->delete();
            Session::flash('success', 'BitcoinTransaction deleted successfully.');
            return redirect()->back();

        }

        if ($page == 'bank') {

            $delete = Bank::find($id);

            if(!$delete)
            {
                abort(404);
            }



            $delete->delete();
            Session::flash('success', 'Bank deleted successfully.');
            return redirect()->back();

        }



        if ($page == 'roles') {

            $delete = Role::find($id);


            if(!$delete)
            {
                abort(404);
            }

            $delete->delete();
            Session::flash('success', 'Role deleted successfully.');
            return redirect()->back();

        }

        if ($page == 'activitytypes') {

            $delete = ActivityType::find($id);


            if(!$delete)
            {
                abort(404);
            }

            $delete->delete();
            Session::flash('success', 'Activity Type deleted successfully.');
            return redirect()->back();

        }


    }




    public function allUsers() {

        $roles  = Role::all();
        $admins = Admin::all();


        return view('cms.userconfig.users', [
            'roles'     =>  $roles,
            'admins'    =>  $admins

        ]);
    }

    public function createAccount(Request $request) {
        $data = array(
            'name'      =>  $request->name,
            'email'     =>  $request->email,
            'password'  => Hash::make($request->password),
            'role_id'   =>  $request->role_id,
        );

        $validator = Validator::make($request->all(), [
            'name'      =>  'required|string|max:191',
            'email'     =>  'required|string|email|max:191|unique:admins',
            'password'  =>  'required|string|min:6|confirmed',
            'role_id'   =>  'required|numeric'
        ], [
            'role_id.required'  =>  'You need to assign a role to this user!'
        ]);

        if($validator->fails()) {
            Session::flash('fail', 'Account could not be created!');
            return redirect()->back()->withErrors($validator);
        }

        Admin::create($data);
        Session::flash('success', 'Admin account successfully created for: ' . $data['name']);

        return redirect()->back();
    }

    public function editAccount(Request $request, $id) {
        $admin = Admin::find($id);
        $roles = Role::all();


        return view('cms.userconfig.users_edit', [
            'admin' =>  $admin,
            'roles' =>  $roles
        ]);
    }

    public function updateAccount(Request $request) {
        $data = array(
            'name'      =>  $request->name,
            'email'     =>  $request->email,
            'role_id'   =>  $request->role_id,
        );

        $validator = Validator::make($request->all(), [
            'name'      =>  'required|string|max:191',
            'email'     =>  'required|string|email|max:191',
            'role_id'   =>  'required|numeric'
        ], [
            'role_id.required'  =>  'You need to assign a role to this user!'
        ]);

        if($validator->fails()) {
            Session::flash('fail', 'Account could not be created!');
            return redirect()->back()->withErrors($validator);
        }

        $admin = Admin::find($request->id);
        $admin->update($data);
        Session::flash('success', 'Admin account successfully updated.');
        return redirect()->route('cms.users.index');
    }

    public function deleteAccount ($id) {
        Admin::destroy($id);
        Session::flash('success', 'Admin Deleted successfully!');
        return redirect()->back();
    }

    public function roleDestroy ($id) {
        Role::destroy($id);
        Session::flash('success', 'Role Deleted successfully!');
        return redirect()->back();
    }

    public function showFAQ ($id) {
        $post = FAQs::findorfail($id);
        return $post;
    }

    public function showDev ($id) {
        $post = Devteam::findorfail($id);
        return $post;
    }

    public function showShoeSize ($id) {
        $post = ShoeSize::findorfail($id);
        return $post;
    }


    public function DeleteCarts ($id) {
        Cart::destroy($id);
        Session::flash('success', 'Cart Deleted successfully!');
        return redirect()->back();
    }


    public function roles(Request $request) {


        $roles = Role::all();

        if($request->query('role')) {
            $id = $request->query('role');
            $role = Role::find($id);
            if(count($role) <= 0) {
                Session::flash('error', 'Role not found!');
                return redirect()->back();
            }
            $permissions = Permission::where('role_id', $role->id)->first();



            return view('cms.userconfig.roles', [
                'roles'         =>  $roles,
                'permission'    =>  $permissions
            ]);
        }

            return view('cms.userconfig.roles', [
            'roles' =>  $roles


        ]);
    }



    public function createRole(Request $request) {
        $data = array(
            'title'         =>  $request->title,
            'description'   =>  $request->description

        );

        $validator = Validator::make($request->all(), [
            'title'         =>  'required|string|max:191',
            'description'   =>  'nullable|string',
        ]);

        if($validator->fails()) {
            Session::flash('fail', 'Role could not be created!');
            return redirect()->back()->withErrors($validator);
        }

        $role = Role::create($data);
        $permissions = array(
            'role_id'              =>  $role->id,
            'home_component'          =>  "no",
            'demo_request'        =>  "no",
            'service'           =>  "no",
            'product'       =>  "no",
            'settings'        =>  "no",
            'pages'     =>  "no",
            'careers'           =>  "no",
            'user_module'            =>  "no",

        );
        Permission::create($permissions);
        Session::flash('success', 'New role successfully created. You can now configure permission for role: ' . $data['title']);
        return redirect()->back();
    }

    public function showColour ($id) {
        $post = Color::findorfail($id);
        return $post;
    }

    public function showClothSize ($id) {
        $post = ClothSize::findorfail($id);
        return $post;
    }

    public function deleteCategory ($id) {
        Category::destroy($id);
        Session::flash('success', 'Category Deleted successfully!');
        return redirect()->back();
      }

      public function deleteSubCategory ($id) {
        SubCategory::destroy($id);
        Session::flash('success', 'SubCategory Deleted successfully!');
        return redirect()->back();
      }

      public function UpdateDev(Request $request, $id) {

        $data = array(
            'name'   => $request->name,
            'email'   => $request->email,

        );

        $update = Devteam::findorfail($request->id);

        $update->update($data);

       Session::flash('success', 'Dev Member Updated successfully.');
        return redirect()
        ->back();

    }


    public function UpdateColour(Request $request, $id) {

        $data = array(
            'name'   => $request->name,
            'code'   => $request->code,

        );

        $update = Color::findorfail($request->id);

        $update->update($data);

       Session::flash('success', 'Color Updated successfully.');
        return redirect()
        ->back();

    }

    public function UpdateShoeSize(Request $request, $id) {

        $data = array(
            'name'   => $request->name,


        );

        $update = ShoeSize::findorfail($request->id);

        $update->update($data);

       Session::flash('success', 'Shoe Size Updated successfully.');
        return redirect()
        ->back();

    }

    public function UpdateClotheSize(Request $request, $id) {

        $data = array(
            'name'   => $request->name,


        );

        $update = ClothSize::findorfail($request->id);

        $update->update($data);

       Session::flash('success', 'Cloth Size Updated successfully.');
        return redirect()
        ->back();

    }

    public function UpdateFAQ(Request $request, $id) {

        $data = array(
            'title'   => $request->title,
            'body'   => $request->body,

        );

        $update = FAQs::findorfail($request->id);

        $update->update($data);

       Session::flash('success', 'FAQ Updated successfully.');
        return redirect()
        ->back();

    }


    public function store (Request $request, $page) {

        if ($page == 'pegg') {

            $validator = Validator::make($request->all(), [
                'label' => ['required'],
            ]);

            if($validator->fails())
            {
                return redirect()->back()->withErrors($validator->errors())->withInput();
            }

            $data = array(
                'product_id'   => $request->product_id,
                'video_id' => $request->video_id,
                'label' => $request->label,
                'confidence' => $request->confidence,
                'confidence' => $request->confidence,
                'boundingBoxHeight' => $request->boundingBoxHeight,
                'boundingBoxWidth' => $request->boundingBoxWidth,
                'boundingBoxLeft' => $request->boundingBoxLeft,
                'duration' => $request->duration,
                'frame_rate' => $request->frame_rate,
                'centroid' => $request->centroid,
                'duration' => $request->duration,
                'millisecond' => $request->millisecond,
            );

            Pegg::create($data);

            Session::flash('success', 'Pegg created successfully.');

            return redirect()->back();

        }

        if ($page == 'coupon') {

            $validator = Validator::make($request->all(), [
                'coupon_code' => ['required'],
            ]);

            if($validator->fails())
            {
                return redirect()->back()->withErrors($validator->errors())->withInput();
            }

            $data = array(
                'coupon_code'   => $request->coupon_code,
                'coupon_type' => $request->coupon_type,
                'coupon_value' => $request->coupon_value,
                'coupon_start_date' => $request->coupon_start_date,
                'coupon_end_date' => $request->coupon_end_date,
                'coupon_max_limit' => $request->coupon_max_limit,
                'min_eligibility_amt' => $request->min_eligibility_amt,
                'coupon_status' => 1,
                'coupon_total_usage' => $request->coupon_total_usage,
                'user_usage_limit' => $request->user_usage_limit,

            );

            Coupon::create($data);

            Session::flash('success', 'Coupon created successfully.');

            return redirect()->back();

        }

        if ($page == 'devteam') {

            $validator = Validator::make($request->all(), [
                'name' => ['required'],
                'email' => ['required'],
            ]);

            if($validator->fails())
            {
                return redirect()->back()->withErrors($validator->errors())->withInput();
            }
            $key = Str::random(36);

            $data = array(
                'name'   => $request->name,
                'email' => $request->email,
                'dev_key' => $key,
            );

            Devteam::create($data);

            Session::flash('success', 'Dev Member created successfully.');

            return redirect()->back();

        }

        if ($page == 'cart') {

            $validator = Validator::make($request->all(), [
                'user_id' => ['required'],
            ]);

            if($validator->fails())
            {
                return redirect()->back()->withErrors($validator->errors())->withInput();
            }

            $data = array(
                'user_id'   => $request->user_id,
                'product_id' => $request->product_id,
                'size' => $request->size,
                'weight' => $request->weight,
                'quantity' => $request->quantity,
                'color_id' => $request->color_id,
            );

            Cart::create($data);

            Session::flash('success', 'Cart created successfully.');

            return redirect()->back();

        }

        if ($page == 'service') {

            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:191',
                'pictures'         => 'nullable', 'mimes:jpg,bmp,png,jpeg,svg'

            ]);

            $user = new Service();
            $user->user_id = $request->user_id;
            $user->name  = $request->name;
            $user->category_id      = $request->category_id;
            $user->company_name      = $request->company_name;
            $user->phone      = $request->phone;
            $user->email      = $request->email;
            $user->address      = $request->address;
            $user->currency      = "₦";
            $user->description      = $request->description;


            if($request->hasFile('pictures')){

                $file = $request->file('pictures');
                $disk = 's3';
                $ext = $file->getClientOriginalExtension();
                $path = 'service'.time().'.'.$ext;


                $storage = Storage::disk($disk)->putFileAs('services/pictures/',$file,$path);
                $db_location = 'https://d3t7szus8c85is.cloudfront.net/services/pictures/' . $path;

                // $input['profile_image'] = $storage;
                $exists = Storage::disk('s3')->get($storage);
                $store = '';
                if($exists){
                    $store = Storage::disk('s3')->url($storage);
                }

                //return $store;
                $user['pictures']  = $db_location;

            }

            $user->save();



            $group = new ServicePackage();
            $group->service_id = $user->id;
            $group->name      = $request->name;
            $group->price      = $request->price;
            $group->description  = $request->description;
            $group->save();

            $group = new ServiceActivity();
            $group->service_id = $user->id;
            $group->service_type_id = $request->service_type_id;
            $group->save();

            $group = new ServiceAvailability();
            $group->service_id = $user->id;
            $group->start_date = $request->start_date;
            $group->end_date = $request->end_date;
            $group->start_time = $request->start_time;
            $group->end_time = $request->end_time;
            $group->save();


            Session::flash('success', 'Service created successfully.');

            return redirect()->back();

        }

        if ($page == 'servicetype') {

            $validator = Validator::make($request->all(), [
                'name' => ['required'],
            ]);

            if($validator->fails())
            {
                return redirect()->back()->withErrors($validator->errors())->withInput();
            }
            $slug = Str::slug($request->name);

            $data = array(
                'name'   => $request->name,
                'slug'   => $slug,

            );
            if($request->hasFile('icon')){

                $file = $request->file('icon');
                $disk = 's3';
                $ext = $file->getClientOriginalExtension();
                $path = 'servicetype'.time().'.'.$ext;


                $storage = Storage::disk($disk)->putFileAs('services/icons/',$file,$path);
                $db_location = 'https://d3t7szus8c85is.cloudfront.net/services/icons/' . $path;

                // $input['profile_image'] = $storage;
                $exists = Storage::disk('s3')->get($storage);
                $store = '';
                if($exists){
                    $store = Storage::disk('s3')->url($storage);
                }

                //return $store;
                $data['icon']  = $db_location;

            }

            ServiceType::create($data);

            Session::flash('success', 'ServiceType created successfully.');

            return redirect()->back();

        }

        if ($page == 'videotag') {

            $validator = Validator::make($request->all(), [
                'name' => ['required'],
            ]);

            if($validator->fails())
            {
                return redirect()->back()->withErrors($validator->errors())->withInput();
            }
            $slug = Str::slug($request->name);

            $data = array(
                'name'   => $request->name,
                'slug'   => $slug,

            );
            if($request->hasFile('icon')){

                $file = $request->file('icon');
                $disk = 's3';
                $ext = $file->getClientOriginalExtension();
                $path = 'servicetype'.time().'.'.$ext;


                $storage = Storage::disk($disk)->putFileAs('product/videotags/',$file,$path);
                $db_location = 'https://d3t7szus8c85is.cloudfront.net/product/videotags/' . $path;

                // $input['profile_image'] = $storage;
                $exists = Storage::disk('s3')->get($storage);
                $store = '';
                if($exists){
                    $store = Storage::disk('s3')->url($storage);
                }

                //return $store;
                $data['icon']  = $db_location;

            }

            VideoTag::create($data);

            Session::flash('success', 'VideoTag created successfully.');

            return redirect()->back();

        }

        if ($page == 'logistic') {

            $validator = Validator::make($request->all(), [
                'name' => ['required'],
            ]);

            if($validator->fails())
            {
                return redirect()->back()->withErrors($validator->errors())->withInput();
            }
            $slug = Str::random(8);

            $data = array(
                'name'   => $request->name,
                'email'   => $request->email,
                'phone'   => $request->phone,
                'address'   => $request->address,
                'code'   => $slug,
                'password'   => Hash::make($request->password),

            );
            if($request->hasFile('image')){

                $file = $request->file('image');
                $disk = 's3';
                $ext = $file->getClientOriginalExtension();
                $path = 'logistics'.time().'.'.$ext;


                $storage = Storage::disk($disk)->putFileAs('logistics/',$file,$path);
                $db_location = 'https://d3t7szus8c85is.cloudfront.net/logistics/' . $path;

                // $input['profile_image'] = $storage;
                $exists = Storage::disk('s3')->get($storage);
                $store = '';
                if($exists){
                    $store = Storage::disk('s3')->url($storage);
                }

                //return $store;
                $data['image']  = $db_location;

            }

            Logistic::create($data);

            Session::flash('success', 'Logistic created successfully.');

            return redirect()->back();

        }

        if ($page == 'interest') {

            $validator = Validator::make($request->all(), [
                'name' => ['required'],
            ]);

            if($validator->fails())
            {
                return redirect()->back()->withErrors($validator->errors())->withInput();
            }
            $slug = Str::slug($request->name);

            $data = array(
                'name'   => $request->name,
                'phone'   => $request->phone,
                'email'   => $request->email,
                'address'   => $request->address,
                'code'   => $slug,
                'password' => Hash::make($request->password),

            );
            if($request->hasFile('picture')){

                $file = $request->file('picture');
                $disk = 's3';
                $ext = $file->getClientOriginalExtension();
                $path = 'interest'.time().'.'.$ext;


                $storage = Storage::disk($disk)->putFileAs('interest/',$file,$path);
                $db_location = 'https://d3t7szus8c85is.cloudfront.net/interest/' . $path;

                // $input['profile_image'] = $storage;
                $exists = Storage::disk('s3')->get($storage);
                $store = '';
                if($exists){
                    $store = Storage::disk('s3')->url($storage);
                }

                //return $store;
                $data['picture']  = $db_location;

            }
            $currentDate = date('Ymd');
            $currentTime = time();

            //VIDEO UPLOAD FUNCTION
            if($request->video) {
                $videoExtension = $request->video->getClientOriginalExtension();
                if($videoExtension !== 'mp4'){
                    return redirect()->back()->with('error', 'You can only upload mp4 files');
                }
                $file_video = $request->video;
                $video_name = $currentDate.$currentTime.'.'.$videoExtension;

                $path = $file_video->storeAs('interest', $video_name, 's3');
                $db_location = 'https://d3t7szus8c85is.cloudfront.net/interest/' . $path;

                //return $store;
                $data['video']  = $db_location;
            }



            Interest::create($data);

            Session::flash('success', 'Interest created successfully.');

            return redirect()->back();

        }

        if ($page == 'brand') {

            $validator = Validator::make($request->all(), [
                'name' => ['required'],
            ]);

            if($validator->fails())
            {
                return redirect()->back()->withErrors($validator->errors())->withInput();
            }
            $slug = Str::slug($request->name);

            $data = array(
                'name'   => $request->name,
                'slug'   => $slug,

            );
            if($request->hasFile('image')){

                $file = $request->file('image');
                $disk = 's3';
                $ext = $file->getClientOriginalExtension();
                $path = 'brands'.time().'.'.$ext;


                $storage = Storage::disk($disk)->putFileAs('brands/',$file,$path);
                $db_location = 'https://d3t7szus8c85is.cloudfront.net/brands/' . $path;

                // $input['profile_image'] = $storage;
                $exists = Storage::disk('s3')->get($storage);
                $store = '';
                if($exists){
                    $store = Storage::disk('s3')->url($storage);
                }

                //return $store;
                $data['image']  = $db_location;

            }

            Brand::create($data);

            Session::flash('success', 'Brand created successfully.');

            return redirect()->back();

        }

        if ($page == 'bank') {

            $validator = Validator::make($request->all(), [
                'name' => ['required'],
            ]);

            if($validator->fails())
            {
                return redirect()->back()->withErrors($validator->errors())->withInput();
            }
            $data = array(
                'name'   => $request->name,
                'code' => $request->code,
            );

            Bank::create($data);

            Session::flash('success', 'Bank created successfully.');

            return redirect()->back();

        }

        if ($page == 'shoesize') {

            $validator = Validator::make($request->all(), [
                'name' => ['required'],
            ]);

            if($validator->fails())
            {
                return redirect()->back()->withErrors($validator->errors())->withInput();
            }
            $data = array(
                'name'   => $request->name,

            );

            ShoeSize::create($data);

            Session::flash('success', 'ShoeSize created successfully.');

            return redirect()->back();

        }

        if ($page == 'clothsize') {

            $validator = Validator::make($request->all(), [
                'name' => ['required'],
            ]);

            if($validator->fails())
            {
                return redirect()->back()->withErrors($validator->errors())->withInput();
            }
            $data = array(
                'name'   => $request->name,

            );

            ClothSize::create($data);

            Session::flash('success', 'ClothSize created successfully.');

            return redirect()->back();

        }

        if ($page == 'colour') {

            $validator = Validator::make($request->all(), [
                'name' => ['required'],
            ]);

            if($validator->fails())
            {
                return redirect()->back()->withErrors($validator->errors())->withInput();
            }

            $data = array(
                'name'   => $request->name,
                'code' => $request->code,
            );

            Color::create($data);

            Session::flash('success', 'Colour created successfully.');

            return redirect()->back();

        }

        if ($page == 'order') {

            $validator = Validator::make($request->all(), [
                'amount' => ['required'],
            ]);

            if($validator->fails())
            {
                return redirect()->back()->withErrors($validator->errors())->withInput();
            }


            $slug = Str::random(8);
            $orderid = Str::random(8);

            $ref = '51' . substr(uniqid(mt_rand(), true), 0, 8);

            $user = new Order();
            // $user->user_id = $request->user_id;
            $user->user_id   = $request->input('user_id');
            $user->reference   = 'edk-'. $ref;
            $user->orderid   = $orderid;
            $user->slug   = $slug;
            $user->amount      = $request->amount;
            $user->note      = $request->note;

            $user->payment_type      = $request->payment_type;
            $user->address      = $request->address;
            $user->weight      = $request->weight;
            $user->delivery_status      = 0;
            $user->payment_status     = 0;
            $user->status     = 'Unpaid';


            $user->save();

            $group = new OrderContent();
            $group->order_id = $user->id;
            $user->reference   = 'edk-'. $ref;
            $group->size      = $request->size;
            $group->color      = $request->color;
            $group->price      = $request->price;
            $group->quantity      = $request->quantity;
            $group->product_id  = $request->product_id;
            $group->save();


            Session::flash('success', 'Order successfully created.');

            return redirect()->back();

        }

        if ($page == 'activitytype') {

            $validator = Validator::make($request->all(), [
                'name' => ['required'],
            ]);

            if($validator->fails())
            {
                return redirect()->back()->withErrors($validator->errors())->withInput();
            }

            $slug = Str::slug($request->name);

            $data = array(
                'name'   => $request->name,
                'description'   => $request->description,
                'slug' => $slug,

            );

            ActivityType::create($data);

            Session::flash('success', 'Activity Type successfully created.');

            return redirect()->back();

        }

        if ($page == 'package') {

            $slug = Str::slug($request->name);

            $validator = Validator::make($request->all(), [
                'name' => ['required'],
            ]);

            if($validator->fails())
            {
                return redirect()->back()->withErrors($validator->errors())->withInput();
            }

            $slug = Str::slug($request->name);

            $data = array(
                'name'   => $request->name,
                'description'   => $request->description,
                'slug' => $slug,

            );

            ServicePlan::create($data);

            Session::flash('success', ' Service Plan successfully created.');

            return redirect()->back();

        }

        if ($page == 'subcategory') {

            $validator = Validator::make($request->all(), [
                'name' => ['required'],
            ]);

            if($validator->fails())
            {
                return redirect()->back()->withErrors($validator->errors())->withInput();
            }

            $slug = Str::slug($request->name);

            $data = array(
                'name'   => $request->name,
                'category_id'   => $request->category_id,
                'description'   => $request->description,
                'image'   => $request->image,
                'slug' => $slug,


            );

            if($request->hasFile('image')){

                $file = $request->file('image');
                $disk = 's3';
                $ext = $file->getClientOriginalExtension();
                $path = 'subcategory'.time().'.'.$ext;


                $storage = Storage::disk($disk)->putFileAs('subcategories/',$file,$path);
                $db_location = 'https://d3t7szus8c85is.cloudfront.net/subcategories/' . $path;

                // $input['profile_image'] = $storage;
                $exists = Storage::disk('s3')->get($storage);
                $store = '';
                if($exists){
                    $store = Storage::disk('s3')->url($storage);
                }

                //return $store;
                $data['image']  = $db_location;

            }

            SubCategory::create($data);

            Session::flash('success', ' SubCategory successfully created.');

            return redirect()->back();

        }

        if ($page == 'category') {

            $validator = Validator::make($request->all(), [
                'name' => ['required'],
            ]);

            if($validator->fails())
            {
                return redirect()->back()->withErrors($validator->errors())->withInput();
            }

            $slug = Str::slug($request->name);

            $data = array(
                'name'   => $request->name,
                'description'   => $request->description,
                'image'   => $request->image,
                'slug' => $slug,


            );

            if($request->hasFile('image')){

                $file = $request->file('image');
                $disk = 's3';
                $ext = $file->getClientOriginalExtension();
                $path = 'category'.time().'.'.$ext;


                $storage = Storage::disk($disk)->putFileAs('categories/',$file,$path);
                $db_location = 'https://d3t7szus8c85is.cloudfront.net/categories/' . $path;

                // $input['profile_image'] = $storage;
                $exists = Storage::disk('s3')->get($storage);
                $store = '';
                if($exists){
                    $store = Storage::disk('s3')->url($storage);
                }

                //return $store;
                $data['image']  = $db_location;

            }

            Category::create($data);

            Session::flash('success', ' Category successfully created.');

            return redirect()->back();

        }

        if ($page == 'shop') {

            $validator = Validator::make($request->all(), [
                'phone' => 'required|string',
                'company_name'         => 'required|string|unique:shops|between:4,100',
                'email'         => 'required|string' ,
                'user_id'         => 'required|string',
                'city_id'         => 'required' ,
                'category_id'         => 'required' ,
                'state_id'         => 'required'
            ]);

            if($validator->fails())
            {
                return redirect()->back()->withErrors($validator->errors())->withInput();
            }

            $slug = Str::slug($request->company_name);

            $data = array(
                'company_name'   => $request->company_name,
                'user_id'   => $request->user_id,
                'phone'   => $request->phone,
                'email'   => $request->email,
                'address'   => $request->address,
                'city_id'   => $request->city_id,
                'state_id'   => $request->state_id,
                'logistic_id'   => $request->logistic_id,
                'delivery_status'   => $request->delivery_status,
                'category_id'   => $request->category_id,
                'status'   => 0,
                'slug' => $slug,

            );

            if($request->hasFile('logo')){

                $file = $request->file('logo');
                $disk = 's3';
                $ext = $file->getClientOriginalExtension();
                $path = 'shop'.time().'.'.$ext;


                $storage = Storage::disk($disk)->putFileAs('shops/logos/',$file,$path);
                $db_location = 'https://d3t7szus8c85is.cloudfront.net/shops/logos/' . $path;

                // $input['profile_image'] = $storage;
                $exists = Storage::disk('s3')->get($storage);
                $store = '';
                if($exists){
                    $store = Storage::disk('s3')->url($storage);
                }

                //return $store;
                $data['logo']  = $db_location;

            }

            Shop::create($data);

            Session::flash('success', ' Shop successfully created.');

            return redirect()->back();

        }

        if ($page == 'servicecategory') {

            $validator = Validator::make($request->all(), [
                'name' => ['required'],
            ]);

            if($validator->fails())
            {
                return redirect()->back()->withErrors($validator->errors())->withInput();
            }

            $slug = Str::slug($request->name);

            $data = array(
                'name'   => $request->name,
                'description'   => $request->description,
                'icon'   => $request->icon,
                'slug' => $slug,

            );

            if($request->hasFile('icon')){

                $file = $request->file('icon');
                $disk = 's3';
                $ext = $file->getClientOriginalExtension();
                $path = 'servicecategory'.time().'.'.$ext;


                $storage = Storage::disk($disk)->putFileAs('services/categories/',$file,$path);
                $db_location = 'https://d3t7szus8c85is.cloudfront.net/services/categories/' . $path;

                // $input['profile_image'] = $storage;
                $exists = Storage::disk('s3')->get($storage);
                $store = '';
                if($exists){
                    $store = Storage::disk('s3')->url($storage);
                }

                //return $store;
                $data['icon']  = $db_location;

            }

            ServiceCategory::create($data);

            Session::flash('success', ' Service Category successfully created.');

            return redirect()->back();

        }

        if ($page == 'promvideo') {

            $validator = Validator::make($request->all(), [
                'title' => 'required|string|max:191',
                'thumbnail'         => 'nullable', 'mimes:jpg,bmp,png,jpeg,svg',
                'video_link'     => 'nullable|mimes:avi,mp4',
            ]);

            $user = new PromotionalVideo();

            $user->user_id   = $request->user_id;
            $user->title      = $request->title;
            $user->product_id      = $request->product_id;
            $user->sub_category_id      = $request->sub_category_id;
            $user->category_id      = $request->category_id;
            $user->status     = 0;



            if($request->hasFile('video_link')){

                $file = $request->file('video_link');
                $disk = 's3';
                $ext = $file->getClientOriginalExtension();
                $path = 'promotional-video'.time().'.'.$ext;


                $storage = Storage::disk('Edekees3New')->putFileAs('edekee-m3u8/',$file,$path);

                $db_location = 'https://edekee-m3u8.s3.us-east-2.amazonaws.com/' . $path;

                // $input['profile_image'] = $storage;
                $exists = Storage::disk('Edekees3New')->get($storage);
                $store = '';
                if($exists){
                    $store = Storage::disk('Edekees3New')->url($storage);
                }
                return $store;
                $user['video_link']  = $db_location;

            }

            if($request->hasFile('thumbnail')){

                $file = $request->file('thumbnail');
                $disk = 's3';
                $ext = $file->getClientOriginalExtension();
                $path = 'promotional-video'.time().'.'.$ext;


                $storage = Storage::disk($disk)->putFileAs('edekee-m3u8/',$file,$path);
                $db_location = 'https://s3.console.aws.amazon.com/s3/buckets/edekee-m3u8/' . $path;

                // $input['profile_image'] = $storage;
                $exists = Storage::disk('s3')->get($storage);
                $store = '';
                if($exists){
                    $store = Storage::disk('s3')->url($storage);
                }

                //return $store;
                $user['thumbnail']  = $db_location;

            }

            $user->save();

            $group = new PromotionalVideoProduct();
            $group->promotional_video_id = $user->id;
            $group->product_id      = $user->product_id;
            $group->save();


            Session::flash('success', ' Promotional Video successfully created.');

            return redirect()->back();

        }
        if ($page == 'product') {


            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:191',
                'picture'         => 'nullable', 'mimes:jpg,bmp,png,jpeg,svg',
                'thumbnail'         => 'nullable', 'mimes:jpg,bmp,png,jpeg,svg',
                'video'     => 'nullable|mimes:avi,mp4',
            ]);


            $slug = Str::slug($request->name);

            $user = new Product();

            $user->user_id   = $request->user_id;
            $user->slug   = $slug;
            $user->name      = $request->name;
            $user->description      = $request->description;
            $user->price      = $request->price;
            $user->sub_categories_id      = $request->sub_categories_id;
            $user->category_id      = $request->category_id;
            $user->quantity      = $request->quantity;
            $user->availability      = 1;
            $user->brand      = $request->brand;
            $user->shop_id      = $request->shop_id;
            $user->is_pinned      = $request->is_pinned;
            $user->sale_status      = $request->sale_status;
            $user->product_status      = 1;
            $user->status     = 1;

            $user->save();

            $group = new ProductAttribute();
            $group->product_id = $user->id;
            $group->size      = $request->size;
            $group->color      = $request->color;
            $group->weight      = $request->weight;
            $group->availability      = 1;
            $group->status      = 1;
            $group->save();

            $groupvideo = new ProductVideo();
            $groupvideo->id = $user->id;
            $groupvideo->product_id = $user->id;
            $groupvideo->user_id      = $user->user_id;
            $groupvideo->status      = 1;

            $currentDate = date('Ymd');
            $currentTime = time();

            //VIDEO UPLOAD FUNCTION
            if($request->video) {
                $videoExtension = $request->video->getClientOriginalExtension();
                if($videoExtension !== 'mp4'){
                    return redirect()->back()->with('error', 'You can only upload mp4 files');
                }
                $file_video = $request->video;
                $video_name = $currentDate.$currentTime.'.'.$videoExtension;

                $path = $file_video->storeAs('edekee/360v', $video_name, 's3');
                $db_location = 'https://d3t7szus8c85is.cloudfront.net/edekee/360v/' . $path;

                //return $store;
                $groupvideo['video']  = $db_location;
            }

             //thumbnail
                if($request->thumbnail){
                    $thumbnailExtension = $request->thumbnail->getClientOriginalExtension();
                    $file_thumbnail = $request->thumbnail;

                    $thumbnail_name = $currentDate.$currentTime.'.'.$thumbnailExtension;

                    $path = $file_thumbnail->storeAs('edekee/thumbnails/', $thumbnail_name, 's3');

                    $db_location = 'https://d3t7szus8c85is.cloudfront.net/' . $path;

                    $groupvideo['thumbnail']  = $db_location;
                }

            $groupvideo->save();

            $grouppic = new ProductPicture();
            $grouppic->id = $user->id;
            $grouppic->product_id = $user->id;
            $grouppic->status      = 0;

            if($request->hasFile('picture')){

                $file = $request->file('picture');
                $disk = 's3';
                $ext = $file->getClientOriginalExtension();
                $path = 'product-picture'.time().'.'.$ext;


                $storage = Storage::disk($disk)->putFileAs('edekee/pictures/',$file,$path);
                $db_location = 'https://d3t7szus8c85is.cloudfront.net/edekee/pictures/' . $path;

                // $input['profile_image'] = $storage;
                $exists = Storage::disk('s3')->get($storage);
                $store = '';
                if($exists){
                    $store = Storage::disk('s3')->url($storage);
                }

                //return $store;
                $grouppic['picture']  = $db_location;

            }

            if($request->hasFile('thumbnail')){

                $file = $request->file('thumbnail');
                $disk = 's3';
                $ext = $file->getClientOriginalExtension();
                $path = 'product'.time().'.'.$ext;


                $storage = Storage::disk($disk)->putFileAs('edekee/thumbnails/',$file,$path);
                $tb_location = 'https://d3t7szus8c85is.cloudfront.net/edekee/thumbnails/' . $path;

                // $input['profile_image'] = $storage;
                $exists = Storage::disk('s3')->get($storage);
                $store = '';
                if($exists){
                    $store = Storage::disk('s3')->url($storage);
                }
          //return $store;
                $grouppic['thumbnail']  = $tb_location;

            }
            $grouppic->save();

            Session::flash('success', 'Product successfully created.');

            return redirect()->back();

        }
    }

    public function Editcart($id)

    {
         $cart = Cart::find($id);
         $colors = Color::orderBy('name')->get();

        return view('cms.utility.edit_cart',compact('cart', 'colors'));
    }

    public function showBank ($id) {
        $post = Bank::findorfail($id);
        return $post;
    }

    public function UpdateCart(Request $request, $id) {


            $prescription = Cart::find($id);
            $prescription->quantity = $request->quantity;
            $prescription->weight = $request->weight;
            $prescription->size = $request->size;
            $prescription->color_id = $request->color_id;

            $prescription->save();

            Session::flash('success', 'Cart updated successfully.');
            return redirect()->back();

    }


    public function UpdateBank(Request $request, $id) {

        $data = array(
            'name'   => $request->name,
            'code'   => $request->code,

        );

        $update = Bank::findorfail($request->id);

        $update->update($data);

       Session::flash('success', 'Bank Updated successfully.');
        return redirect()
        ->back();

    }




}
