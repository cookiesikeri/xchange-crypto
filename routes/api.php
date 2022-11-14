<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\Savings\AgentSavingsController;
use App\Http\Controllers\Apis\AirtimeController;
use App\Http\Controllers\Apis\DataController;
use App\Http\Controllers\Apis\PowerController;
use App\Http\Controllers\Apis\TVController;
use App\Http\Controllers\Apis\UtilityController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PasswordResetRequestController;
use App\Http\Controllers\Apis\UserController as ApisUserController;
use App\Http\Controllers\PermissionController;
use App\Models\PosTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\TransactionController;

use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Permission;
use Tymon\JWTAuth\Facades\JWTAuth;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

/*
|
| Modularization of Other routes
| Loan Routes are loaded from ../api/loans.php
|
*/



Route::group(['prefix' => 'v1', 'middleware' => ['signature']], function(){

    //Auth apis
    Route::post('register/{referral_code}', [UserController::class, 'register']);
    Route::post('register', [UserController::class, 'register']);
    Route::post('register/verification', [UserController::class, 'AccountVerification']);
    Route::post('forgot_password', [PasswordResetRequestController::class, 'forgotPassword']);
    Route::post('verify_password_token', [PasswordResetRequestController::class, 'verifyToken']);
    Route::post('password_reset', [PasswordResetRequestController::class, 'resetPassword']);
    Route::post('send-otp', [UserController::class, 'sendOTP']);
    Route::post('verify-otp', [UserController::class, 'verifyOtp']);
    Route::post('resend-otp', [UserController::class, 'resendOtp']);

    Route::post('login', [UserController::class, 'login']);
    Route::post('logout', [UserController::class, 'logout']);


// all routes that needs the cors middlewares added
    Route::middleware(['cors'])->group(function () {
        Route::post('users/{user}', [UserController::class, 'update']);

        Route::post('verify_bvn', [UserController::class, 'verifyBVN']);
        Route::post('/contact-us', [ApiController::class, 'ContactUs']);
        Route::get('general_details', [ApiController::class, 'GeneralDetail']);
        Route::get('site_settings', [ApiController::class, 'SiteSetting']);
        Route::get('states', [ApiController::class, 'States']);
        Route::get('countries', [ApiController::class, 'Country']);
        Route::get('lgas', [ApiController::class, 'LGA']);
        Route::get('faqs', [ApiController::class, 'FAQs']);
    });


        Route::prefix('bills')->group( function() {
            // all airtime routes group
            Route::prefix('airtime')->name('airtime.')->group(function () {
                Route::post('request', [AirtimeController::class, 'request'])->name('request');
            });

            // all data routes group
            Route::prefix('data')->name('data.')->group(function () {
                Route::get('bundles/{networkID}', [DataController::class, 'getBundles'])->name('bundles.get');
                Route::post('request', [DataController::class, 'request'])->name('bundles.get');
            });

            // all power routes group
            Route::prefix('power')->name('power.')->group(function () {
                Route::post('meter-info', [PowerController::class, 'getMeterInfo'])->name('get-meter-info');
                Route::post('request', [PowerController::class, 'request'])->name('request');
            });

            // all tv routes group
            Route::prefix('tv')->name('tv.')->group(function () {
                Route::get('info/{providerID}', [TVController::class, 'getTVInfo'])->name('get-tv-info');
                Route::post('info', [TVController::class, 'getCardInfo'])->name('get-card-info');
                Route::post('request', [TVController::class, 'request'])->name('request');
            });

        });

        // all services routes group
        Route::prefix('services')->name('service.')->group(function () {
            Route::get('get-service/{id}', [UtilityController::class, 'getService'])->name('get');
        });

        // alll users routes group
        Route::name('users.')->group(function () {
            Route::get('users', [ApisUserController::class, 'index'])->name('index');
            Route::get('users/null-wallets', [ApisUserController::class, 'indexNull'])->name('index_null');
            Route::get('is_user/{user_id}', [ApisUserController::class, 'is_user'])->name('is_user');
            Route::post('edit_profile', [ApisUserController::class, 'edit_profile'])->name('edit_profile');
            Route::post('edit_logon', [ApisUserController::class, 'edit_logon'])->name('edit_logon');

            Route::post('verify_account_number', [ApisUserController::class, 'verifyAccountNumber']);
            Route::get('verify_wallet_account_number/{account_number}', [ApisUserController::class, 'verifyWalletAccountNumber']);

            Route::post('transfer_status', [ApisUserController::class, 'transferStatus'])->middleware('bvn');
            Route::post('set_transaction_pin', [ApisUserController::class, 'setTransactionPin']);
            Route::post('update_transaction_pin', [ApisUserController::class, 'updateTransactionPin']);

            Route::get('sent_transfer_history/{user_id}', [ApisUserController::class, 'sentTransferHistory']);
            Route::get('transaction_history/{user_id}', [ApisUserController::class, 'getTransferTransactionHistory']);
            //Route::get('transaction_history_month/{user_id}/{month}', [ApisUserController::class, 'getMonthTransaction']);
            Route::get('received_transfer_history/{user_id}', [ApisUserController::class, 'receivedTransferHistory']);
            Route::get('banks', [ApisUserController::class, 'getBanksList']);

//            Route::post('repay-loan', [ApisUserController::class, 'RepayLoan'])->name('repay-loan');

            Route::get('get_user_loan_balance/{user_id}', [ApisUserController::class, 'get_user_loan_balance'])->name('get_user_loan_balance');
            Route::get('get_user_wallet_balance/{user_id}', [ApisUserController::class, 'get_user_wallet_balance'])->name('get_user_wallet_balance');
            Route::get('user_has_sufficient_wallet_balance/{user_id}/{amount}', [ApisUserController::class, 'user_has_sufficient_wallet_balance'])->name('user_has_sufficient_wallet_balance');
            Route::get('update_user_wallet_balance/{user_id}/{amount}', [ApisUserController::class, 'update_user_wallet_balance'])->name('update_user_wallet_balance');
            Route::get('get_user_power_transactions/{user_id}/{paginate}/{status}', [ApisUserController::class, 'get_user_power_transactions'])->name('get_user_power_transactions');
            Route::get('get_user_all_power_transactions/{user_id}/{status}', [ApisUserController::class, 'get_user_all_power_transactions'])->name('get_user_all_power_transactions');
            Route::get('get_user_airtime_transactions/{user_id}/{paginate}/{status}', [ApisUserController::class, 'get_user_airtime_transactions'])->name('get_user_airtime_transactions');
            Route::get('get_user_all_airtime_transactions/{user_id}/{status}', [ApisUserController::class, 'get_user_all_airtime_transactions'])->name('get_user_all_airtime_transactions');
            Route::get('get_user_data_transactions/{user_id}/{paginate}/{status}', [ApisUserController::class, 'get_user_data_transactions'])->name('get_user_data_transactions');
            Route::get('get_user_all_data_transactions/{user_id}/{status}', [ApisUserController::class, 'get_user_all_data_transactions'])->name('get_user_all_data_transactions');
            Route::get('get_user_tv_transactions/{user_id}/{paginate}/{status}', [ApisUserController::class, 'get_user_tv_transactions'])->name('get_user_tv_transactions');
            Route::get('get_user_all_tv_transactions/{user_id}/{status}', [ApisUserController::class, 'get_user_all_tv_transactions'])->name('get_user_all_tv_transactions');
            Route::get('user/all/bill-transactions/{user_id}/{bill}', [ApisUserController::class, 'allUsersBillTransaction'])->name('get_user_all_bill_transactions');
            Route::get('generate_transaction_reference', [ApisUserController::class, 'generate_transaction_reference'])->name('generate_transaction_reference');
            Route::get('secret_question_and_answer/{user_id}', [ApisUserController::class, 'getUserSecretQuestion']);
            Route::post('secret_question_and_answer', [ApisUserController::class, 'setSecretQandA']);
            Route::post('change_pin/get_otp', [ApisUserController::class, 'initChangePin']);
            Route::post('save/beneficiary', [ApisUserController::class, 'saveBeneficiary']);
            Route::post('remove/beneficiary', [ApisUserController::class, 'removeBeneficiary']);
            Route::get('beneficiaries/{user_id}', [ApisUserController::class, 'getBeneficiaries']);
            Route::get('referrals/{user_id}', [ApisUserController::class, 'getReferrals']);
        });

        //
        Route::get('generate-locator', function () {
            $digits_needed = 12;
            $random_number = '';
            $count = 0;
            while ($count < $digits_needed) {
                $random_digit = mt_rand(0, 9);
                $random_number .= $random_digit;
                $count++;
            }
            return response()->json($random_number);
        });


        Route::prefix('savings')->name('savings.')->group(function () {
            Route::get('{id}', [SavingController::class, 'getSavingAccount']);
            //Users Saved Cards
            Route::get('cards/{id}', [SavingController::class, 'getCards']);
            Route::post('card/delete', [SavingController::class, 'deleteCard']);

            //Personal Savings
            Route::prefix('personal')->name('personal.')->group(function () {
                Route::post('create', [SavingController::class, 'initSave']);
                Route::get('accounts/{userId}', [SavingController::class, 'listAccounts']);
                Route::get('account/{id}', [SavingController::class, 'getAccount']);
                Route::post('account/close', [SavingController::class, 'closeAccount'])->middleware('bvn');
                Route::post('account/withdraw', [SavingController::class, 'withdrawAccount'])->middleware('bvn');
                Route::post('fund/card', [SavingController::class, 'fundSavingsAccountFromCard']);
                Route::post('fund/wallet', [SavingController::class, 'fundSavingsAccountFromWallet']);
                Route::post('fund/transfer', [SavingController::class, 'fundSavingsAccountFromTransfer']);
                Route::get('account/history/{account_id}', [SavingController::class, 'getAccountHistory']);
                Route::post('extend', [SavingController::class, 'extendSavings']);
            });


    });

//Route::post('business/register', [BusinessController::class, 'register'])->middleware('cors');

//    Route::webhooks('paystack-webhook');
});

Route::fallback(function(Request $request){
    return $response = [
        'status' => 404,
        'code' => '004',
        'title' => 'route does not exist',
        'source' => array_merge($request->all(), ['path' => $request->getPathInfo()])
    ];
});
