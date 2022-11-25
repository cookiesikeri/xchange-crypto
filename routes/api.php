<?php


use App\Http\Controllers\Apis\AirtimeController;
use App\Http\Controllers\Apis\DataController;
use App\Http\Controllers\Apis\UtilityController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PasswordResetRequestController;
use App\Http\Controllers\Apis\UserController as ApisUserController;
use App\Http\Controllers\ApiController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\BitconWalletController;
use App\Http\Controllers\DogecoinController;
use App\Http\Controllers\EtherumController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;


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


});

Route::group(['prefix' => 'v1', 'middleware' => ['cors']], function(){

// all routes that needs the cors middlewares added
    Route::post('logout', [UserController::class, 'logout']);

    Route::post('users/{user}', [UserController::class, 'update']);

    Route::post('verify_bvn', [UserController::class, 'verifyBVN']);
    Route::post('/contact-us', [ApiController::class, 'ContactUs']);
    Route::get('general_details', [ApiController::class, 'GeneralDetail']);
    Route::get('site_settings', [ApiController::class, 'SiteSetting']);
    Route::get('states', [ApiController::class, 'States']);
    Route::get('countries', [ApiController::class, 'Country']);
    Route::get('lgas', [ApiController::class, 'LGA']);
    Route::get('faqs', [ApiController::class, 'FAQs']);
    Route::get('security/questions', [ApiController::class, 'SeqQuetions']);


    Route::post('request-physicalcard', [ApisUserController::class, 'RequestPhysicalCard']);
    Route::post('request-virtuallcard', [ApisUserController::class, 'RequestVirtualCard']);


    //users
    Route::post('/user/secret_question_and_answer', [ApisUserController::class, 'setSecretQandA']);
    Route::post('user/set_transaction_pin', [ApisUserController::class, 'setTransactionPin']);
    Route::post('change_pin/get_otp', [ApisUserController::class, 'initChangePin']);
    Route::post('update_transaction_pin', [ApisUserController::class, 'updateTransactionPin']);

    //transfer
    Route::get('banks', [TransactionController::class, 'getBanksList']);
    Route::get('bank/resolve', [TransactionController::class, 'verifyAccountNumber']);
    Route::post('transferrecipient', [TransactionController::class, 'TransferRecipient']);

    Route::post('fund_user_wallet/card', [ApisUserController::class, 'fund_user_wallet_card'])->name('fund_user_wallet');
    Route::post('fund_user_wallet/transfer', [ApisUserController::class, 'fund_user_wallet_transfer']);
    Route::get('user/secret_question_and_answer/{user_id}', [ApisUserController::class, 'getUserSecretQuestion']);
    Route::get('get_user_wallet_balance/{user_id}', [ApisUserController::class, 'get_user_wallet_balance'])->name('get_user_wallet_balance');

    Route::get('user_has_sufficient_wallet_balance/{user_id}/{amount}', [ApisUserController::class, 'user_has_sufficient_wallet_balance'])->name('user_has_sufficient_wallet_balance');
    Route::get('update_user_wallet_balance/{user_id}/{amount}', [ApisUserController::class, 'update_user_wallet_balance'])->name('update_user_wallet_balance');
    Route::get('get_user_airtime_transactions/{user_id}/{paginate}/{status}', [ApisUserController::class, 'get_user_airtime_transactions'])->name('get_user_airtime_transactions');
    Route::get('get_user_all_airtime_transactions/{user_id}/{status}', [ApisUserController::class, 'get_user_all_airtime_transactions'])->name('get_user_all_airtime_transactions');
    Route::get('get_user_data_transactions/{user_id}/{paginate}/{status}', [ApisUserController::class, 'get_user_data_transactions'])->name('get_user_data_transactions');
    Route::get('get_user_all_data_transactions/{user_id}/{status}', [ApisUserController::class, 'get_user_all_data_transactions'])->name('get_user_all_data_transactions');
    Route::get('user/all/bill-transactions/{user_id}/{bill}', [ApisUserController::class, 'allUsersBillTransaction'])->name('get_user_all_bill_transactions');
    Route::get('generate_transaction_reference', [ApisUserController::class, 'generate_transaction_reference'])->name('generate_transaction_reference');


     //bitcoin
    Route::get('create/bitcoin/wallet', [BitconWalletController::class, 'CreateBitcoinWallet']);
    Route::get('generate/bitcoin/address/{xpub}/{index}', [BitconWalletController::class, 'CreateBitcoinAddress']);
    Route::post('bitcoin/create/privatekey', [BitconWalletController::class, 'CreateBitcoinPrivateKey']);
    Route::get('bitcoin/balance/{address}', [BitconWalletController::class, 'BtcGetBalanceOfAddress']);
    Route::get('bitcoin/all/transaction/{address}', [BitconWalletController::class, 'BtcGetTxByAddress']);
    Route::post('bitcoin/transfer/{privkey}/{senderadd}/{receiverAdd}/{value}', [BitconWalletController::class, 'BtcTransferBlockchain']);
    Route::get('bitcoin/transaction/details/{hash}', [BitconWalletController::class, 'BtcGetTransactionDetails']);
    Route::get('utxo/transaction/details/{hash}/{index}', [BitconWalletController::class, 'BtcGetUTXODetails']);
    Route::get('btc/blockchain/info', [BitconWalletController::class, 'BtcGetBlockChainInfo']);
    Route::get('btc/get/blockhash/{i}', [BitconWalletController::class, 'BtcGetBlockHash']);
    Route::post('btc/broadcast', [BitconWalletController::class, 'BtcBroadcast']);


    //etherum
    Route::get('create/etherum/wallet', [EtherumController::class, 'EthGenerateWallet']);
    Route::get('create/etherum/address/{xpub}', [EtherumController::class, 'EthGenerateAddress']);
    Route::post('etherum/create/privatekey', [EtherumController::class, 'EthGenerateAddressPrivateKey']);
    Route::get('etherum/current/block', [EtherumController::class, 'EthGetCurrentBlock']);
    Route::get('etherum/block/{hash}', [EtherumController::class, 'EthGetBlockByHash']);
    Route::get('etherum/balance/{address}', [EtherumController::class, 'EthGetBalance']);
    Route::get('etherum/transaction/{hash}', [EtherumController::class, 'EthGetTransaction']);
    Route::get('etherum/transaction/{address}', [EtherumController::class, 'EthGetTransactionByAddress']);
    Route::get('etherum/transaction/count/{hash}', [EtherumController::class, 'EthGetTransactionCount']);
    Route::post('etherum/transfer', [EtherumController::class, 'EthBlockchainTransfer']);
    Route::post('etherum/invoke', [EtherumController::class, 'EthBlockchainSmartContractInvocation']);
    Route::get('etherum/internal/transaction/{address}', [EtherumController::class, 'EthGetInternalTransactionByAddress']);
    Route::post('etherum/broadcast', [EtherumController::class, 'EthBroadcast']);


    //dogecoin
    Route::get('create/dogecoin/wallet', [DogecoinController::class, 'DogeGenerateWallet']);
    Route::get('create/dogecoin/address/{xpub}', [DogecoinController::class, 'DogeGenerateAddress']);
    Route::post('dogecoin/create/privatekey', [DogecoinController::class, 'DogeGenerateAddressPrivateKey']);
    Route::get('dogecoin/blockchain/info', [DogecoinController::class, 'DogeGetBlockChainInfo']);



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

//    Route::webhooks('paystack-webhook');


Route::fallback(function(Request $request){
    return $response = [
        'status' => 404,
        'code' => '004',
        'title' => 'route does not exist',
        'source' => array_merge($request->all(), ['path' => $request->getPathInfo()])
    ];
});
