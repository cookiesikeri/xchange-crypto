<?php

use App\Http\Controllers\DashboardController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\ConsumerController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

route::get('404', ['as'=> '404', 'uses' =>
'ErrorController@notfound']);
route::get('500', ['as'=> '505', 'uses' =>
'ErrorController@fatal']);


// Route::get('/', function () {
//     return [
//         'test' => "Welcome to Taheerxchange Nigeria System Api Live APP..."
//     ];
// });


require __DIR__.'/auth.php';
Route::get('/', [HomeController::class, 'index']);;
Route::get('/login', [HomeController::class, 'login'])->name('login');
Route::get('/logout', [HomeController::class, 'logout'])->name('logout');
Route::post('/login', [HomeController::class, 'loginSubmit'])->name('login.submit');
Route::get('/dashboard', [DashboardController::class, 'Index'])->name('dashboard');

Route::post('profile/update/{id}', [AdminController::class, 'AdminProfileUpdate'])->name('cms.admin.update');
Route::post('admin/password/update/{id}', [AdminController::class, 'AdminupdatePassword'])->name('admin.update.password');
Route::put('sitesettings', [AdminController::class, 'site_configuration_update'])->name('cms.site-settings.update');

//messages
Route::get('/all/messages', [MessageController::class, 'message'])->name('cms.messages');
Route::get('/message/read/{id}', [MessageController::class, 'messageRead'])->name('cms.message.markread');
Route::get('/message/unread/{id}', [MessageController::class, 'MarkmessageunRead'])->name('cms.message.markunread');
Route::get('/unread/messages', [MessageController::class, 'messageReadUnread'])->name('cms.message.unread');
Route::get('/read/messages', [MessageController::class, 'Readmessages'])->name('cms.read.messages');
Route::get('/message/details/{id}', [MessageController::class, 'MessageDetails'])->name('cms.message.details');
Route::get('/message/delete/{id}', [MessageController::class, 'messageDelete'])->name('cms.messages.destroy');

//consumers

Route::get('users/this/month', [ConsumerController::class, 'ThisMonthUsers'])->name('cms.thismonth.users');
Route::get('users/registered/today', [ConsumerController::class, 'TodayUsersreg'])->name('cms.users.today');
Route::get('wallet/edit/{id}', [ConsumerController::class, 'WalletEdit'])->name('cms.wallet.edit');
Route::post('register/user', [ConsumerController::class, 'registerUser'])->name('cms.register.user');
Route::post('wallet/update/{id}', [ConsumerController::class, 'WalletUpdate'])->name('cms.wallet.update');
Route::get('all/users', [ConsumerController::class, 'Consumers'])->name('cms.users');
Route::get('deleted/consumers', [ConsumerController::class, 'DeletedConsumers'])->name('cms.deletedconsumers');
Route::get('consumer/edit/{id}', [ConsumerController::class, 'editConsumer'])->name('cms.consumer.edit');
Route::post('consumer/update/{id}', [ConsumerController::class, 'updateConsumer'])->name('cms.consumer.update');
Route::get('consumer/delete/{id}', [ConsumerController::class, 'deleteConsumer'])->name('cms.consumer.delete');
Route::get('add/user', [ConsumerController::class, 'AddUser'])->name('cms.add.user');

Route::get('user-activities', [AdminController::class, 'UserActivities'])->name('cms.user.activities');
Route::get('profile', [AdminController::class, 'AdminProfile'])->name('cms.admin.profile');
Route::get('site/settings', [AdminController::class, 'site_configuration'])->name('cms.settings');
Route::get('security/questions', [AdminController::class, 'SecurityQuestion'])->name('cms.security');
Route::get('user/otp', [AdminController::class, 'PassengersOtp'])->name('cms.otp');
Route::get('site/settings', [AdminController::class, 'site_configuration'])->name('cms.settings');

Route::get('virtual-card-requests', [AdminController::class, 'virtualRequests'])->name('cms.virtual.requests');
Route::get('virtual-accounts', [AdminController::class, 'virtualAccounts'])->name('cms.virtual.accounts');
Route::get('virtual-cards', [AdminController::class, 'virtualCards'])->name('cms.virtual.cards');

Route::get('wallet/manager', [ConsumerController::class, 'WalletManager'])->name('cms.wallet');
Route::get('users/accountnumbers', [ConsumerController::class, 'Accountnumbers'])->name('cms.accountnumber');

    // **action routes

    Route::post('about/{page}', [AdminController::class, 'store'])->name('cms.store');
    Route::get('delete/{page}/{id}', [AdminController::class, 'destroy'])->name('cms.destroy');

    Route::get('explore/{page}/{id}', [AdminController::class, 'show'])->name('cms.show');
    Route::put('explore/{page}/{id}', [AdminController::class, 'update'])->name('cms.update');


    //create user routes ***
    Route::redirect('users', 'users/index', 301);
    Route::get('all/roles', [AdminController::class, 'roles'])->name('cms.users.roles');
    Route::post('roles', [AdminController::class, 'createRole'])->name('cms.roles.store');
    Route::post('roles/roles/permissions/update', [AdminController::class, 'permissionUpdate'])->name('cms.roles.permissions.update');
    Route::get('users/index', [AdminController::class, 'allUsers'])->name('cms.users.index');
    Route::post('users/store', [AdminController::class, 'createAccount'])->name('cms.users.store');
    Route::get('users/edit/{id}', [AdminController::class, 'editAccount'])->name('cms.users.edit');
    Route::put('users/update', [AdminController::class, 'updateAccount'])->name('cms.users.update');
    Route::get('users/delete/{id}', [AdminController::class, 'deleteAccount'])->name('cms.users.delete');
    Route::get('roles/delete/{id}', [AdminController::class, 'roleDestroy'])->name('roles.delete');

    Route::get('airtime', [AdminController::class, 'Aritime'])->name('cms.airtime');
    Route::get('data', [AdminController::class, 'DATa'])->name('cms.data');
    Route::get('tv', [AdminController::class, 'TV'])->name('cms.tv');
    Route::get('power', [AdminController::class, 'Power'])->name('cms.power');
    Route::get('tv/bundles', [AdminController::class, 'Tvplans'])->name('cms.tv.plans');
    Route::get('electricity/discos', [AdminController::class, 'Powerplans'])->name('cms.power.plans');
    Route::get('data/bundles', [AdminController::class, 'dataPlans'])->name('cms.data.plans');

    Route::get('giftcard/customer', [AdminController::class, 'giftcardCustomer'])->name('cms.giftcard.customer');
    Route::get('giftcards', [AdminController::class, 'Giftcards'])->name('cms.giftcards');
    Route::get('giftcard/activities', [AdminController::class, 'giftcardActivities'])->name('cms.giftcard.activities');

    Route::get('btc/wallets', [AdminController::class, 'BTCWallet'])->name('cms.btc.wallets');
    Route::get('bnb/wallets', [AdminController::class, 'BNBWallet'])->name('cms.bnb.wallets');
    Route::get('eth/wallets', [AdminController::class, 'ETHWallet'])->name('cms.eth.wallets');
    Route::get('litecoin/wallets', [AdminController::class, 'LTCWallet'])->name('cms.ltc.wallets');
    Route::get('polygon/wallets', [AdminController::class, 'POLWallet'])->name('cms.pol.wallets');
    Route::get('dogecoin/wallets', [AdminController::class, 'DogecoinWallet'])->name('cms.dog.wallets');

    Route::get('btc/transactions', [AdminController::class, 'BTCtransactions'])->name('cms.btc.transactions');
    Route::get('bnb/transactions', [AdminController::class, 'BNBtransactions'])->name('cms.bnb.transactions');
    Route::get('eth/transactions', [AdminController::class, 'ETHtransactions'])->name('cms.eth.transactions');
    Route::get('litecoin/transactions', [AdminController::class, 'LTCtransactions'])->name('cms.ltc.transactions');
    Route::get('polygon/transactions', [AdminController::class, 'POLtransactions'])->name('cms.pol.transactions');
    Route::get('dogecoin/transactions', [AdminController::class, 'Dogecointransactions'])->name('cms.dog.transactions');
