<?php


use App\Http\Controllers\AirtimeController;
use App\Http\Controllers\DataController;
use App\Http\Controllers\Apis\UtilityController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PasswordResetRequestController;
use App\Http\Controllers\Apis\UserController as ApisUserController;
use App\Http\Controllers\ApiController;
use App\Http\Controllers\BinanceController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\BitconWalletController;
use App\Http\Controllers\DogecoinController;
use App\Http\Controllers\EtherumController;
use App\Http\Controllers\GiftcardController;
use App\Http\Controllers\LitecoinController;
use App\Http\Controllers\PolygonController;
use App\Http\Controllers\PowerController;
use App\Http\Controllers\TVController;
use App\Http\Controllers\VirtualAccountController;
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

    Route::get('request/airtime/', [AirtimeController::class, 'sendSMS']);
});

Route::group(['prefix' => 'v1', 'middleware' => ['cors']], function(){

// all routes that needs the cors middlewares added
    Route::post('logout', [UserController::class, 'logout']);

    Route::post('users/{user}', [UserController::class, 'update']);

    Route::post('verify_bvn/{bvn}/{acct}/{code}', [ApisUserController::class, 'verifyBVN']);
    Route::post('/contact-us', [ApiController::class, 'ContactUs']);
    Route::get('general_details', [ApiController::class, 'GeneralDetail']);
    Route::get('site_settings', [ApiController::class, 'SiteSetting']);
    Route::get('btc/wallets', [ApiController::class, 'BTCwallets']);
    Route::get('eth/wallets', [ApiController::class, 'ETHwallets']);
    Route::get('litcoin/wallets', [ApiController::class, 'LTHwallets']);
    Route::get('bnb/wallets', [ApiController::class, 'BNBwallets']);
    Route::get('polygon/wallets', [ApiController::class, 'POLYGONwallets']);
    Route::get('states', [ApiController::class, 'States']);
    Route::get('countries', [ApiController::class, 'Country']);
    Route::get('lgas', [ApiController::class, 'LGA']);
    Route::get('faqs', [ApiController::class, 'FAQs']);
    Route::get('security/questions', [ApiController::class, 'SeqQuetions']);
    Route::get('check/malicous/address/{address}', [ApiController::class, 'CheckMalicousAddress']);
    Route::get('current/exchange/rate/{currency}', [ApiController::class, 'Exchangerate']);


    //bank
    Route::get('banks', [TransactionController::class, 'getBanksList']);
    Route::get('bank/resolve', [TransactionController::class, 'verifyAccountNumber']);
    Route::post('transferrecipient', [TransactionController::class, 'TransferRecipient']);
    Route::post('register_bvn', [ApisUserController::class, 'registerBVN']);
    Route::post('verify_bvn', [ApisUserController::class, 'verifyBVN']);
    Route::post('update_bvn', [ApisUserController::class, 'updateBVN']);

    //users
    Route::post('/user/secret_question_and_answer', [ApisUserController::class, 'setSecretQandA']);
    Route::post('user/set_transaction_pin', [ApisUserController::class, 'setTransactionPin']);
    Route::post('change_pin/get_otp', [ApisUserController::class, 'initChangePin']);
    Route::post('update_transaction_pin', [ApisUserController::class, 'updateTransactionPin']);

    Route::post('fund_user_wallet/card', [ApisUserController::class, 'fund_user_wallet_card']);
    Route::post('fund_user_wallet/transfer', [ApisUserController::class, 'fund_user_wallet_transfer']);
    Route::get('user/secret_question_and_answer/{user_id}', [ApisUserController::class, 'getUserSecretQuestion']);
    Route::get('get_user_wallet_balance/{user_id}', [ApisUserController::class, 'get_user_wallet_balance'])->name('get_user_wallet_balance');

    Route::get('user_has_sufficient_wallet_balance/{user_id}/{amount}', [ApisUserController::class, 'user_has_sufficient_wallet_balance']);
    Route::get('update_user_wallet_balance/{user_id}/{amount}', [ApisUserController::class, 'update_user_wallet_balance']);
    Route::get('get_user_airtime_transactions/{user_id}/{paginate}/{status}', [ApisUserController::class, 'get_user_airtime_transactions']);
    Route::get('get_user_all_airtime_transactions/{user_id}/{status}', [ApisUserController::class, 'get_user_all_airtime_transactions']);
    Route::get('get_user_data_transactions/{user_id}/{paginate}/{status}', [ApisUserController::class, 'get_user_data_transactions']);
    Route::get('get_user_all_data_transactions/{user_id}/{status}', [ApisUserController::class, 'get_user_all_data_transactions']);
    Route::get('get_user_power_transactions/{user_id}/{paginate}/{status}', [ApisUserController::class, 'get_user_power_transactions']);
    Route::get('get_user_all_power_transactions/{user_id}/{status}', [ApisUserController::class, 'get_user_all_power_transactions']);
    Route::get('get_user_tv_transactions/{user_id}/{paginate}/{status}', [ApisUserController::class, 'get_user_tv_transactions']);
    Route::get('get_user_all_tv_transactions/{user_id}/{status}', [ApisUserController::class, 'get_user_all_tv_transactions']);
    Route::get('user/all/bill-transactions/{user_id}/{bill}', [ApisUserController::class, 'allUsersBillTransaction']);
    Route::get('generate_transaction_reference', [ApisUserController::class, 'generate_transaction_reference']);

    Route::get('transaction_history_today/{user_id}', [ApisUserController::class, 'getTodayTransaction']);
    Route::get('transaction_history_month/{user_id}/{month}', [ApisUserController::class, 'getMonthTransaction']);

    Route::get('sent_transfer_history/{user_id}', [ApisUserController::class, 'sentTransferHistory']);
    Route::get('transaction_history/{user_id}', [ApisUserController::class, 'getTransferTransactionHistory']);
    Route::get('received_transfer_history/{user_id}', [ApisUserController::class, 'receivedTransferHistory']);
    Route::get('verify_wallet_account_number/{account_number}', [ApisUserController::class, 'verifyWalletAccountNumber']);
    Route::post('transfer_status', [ApisUserController::class, 'transferStatus'])->middleware('bvn');
    Route::post('transfer_to_bank_acc', [ApisUserController::class, 'transferToBankAcc']);
    Route::post('wallet/transfer', [ApisUserController::class, 'walletToWalletTransfer'])->middleware('bvn');
    Route::post('wallet/multiple_transfer', [ApisUserController::class, 'multiWalletToWalletTransfer'])->middleware('bvn');

    Route::get('get_user_btc_address/{user_id}', [ApisUserController::class, 'get_user_btc_address']);
    Route::get('get_user_eth_address/{user_id}', [ApisUserController::class, 'get_user_eth_address']);
    Route::get('get_user_litecoin_address/{user_id}', [ApisUserController::class, 'get_user_litecoin_address']);
    Route::get('get_user_polygon_address/{user_id}', [ApisUserController::class, 'get_user_polygon_address']);
    Route::get('get_user_bnb_address/{user_id}', [ApisUserController::class, 'get_user_bnb_address']);
    Route::get('get_user_dogecoin_address/{user_id}', [ApisUserController::class, 'get_user_dogecoin_address']);


     //bitcoin
    Route::post('generate/bitcoin/wallet', [BitconWalletController::class, 'CreateBitcoinWallet']);
    Route::get('generate/bitcoin/address/{xpub}', [BitconWalletController::class, 'CreateBitcoinAddress']);
    Route::post('bitcoin/create/privatekey', [BitconWalletController::class, 'CreateBitcoinPrivateKey']);
    Route::get('bitcoin/balance/{address}', [BitconWalletController::class, 'BtcGetBalanceOfAddress']);
    Route::get('bitcoin/all/transaction/{address}', [BitconWalletController::class, 'BtcGetTxByAddress']);
    Route::post('bitcoin/transfer/{privkey}/{senderadd}/{receiverAdd}/{value}', [BitconWalletController::class, 'BtcTransferBlockchain']);
    Route::get('bitcoin/transaction/details/{hash}', [BitconWalletController::class, 'BtcGetTransactionDetails']);
    Route::get('utxo/transaction/details/{hash}/{index}', [BitconWalletController::class, 'BtcGetUTXODetails']);
    Route::get('btc/blockchain/info', [BitconWalletController::class, 'BtcGetBlockChainInfo']);
    Route::get('btc/get/blockhash/{i}', [BitconWalletController::class, 'BtcGetBlockHash']);
    Route::post('btc/broadcast', [BitconWalletController::class, 'BtcBroadcast']);
    Route::post('btc/gas/fee', [BitconWalletController::class, 'BtcEstimateGas']);
    Route::get('bitcoin/private/key/{user_id}', [BitconWalletController::class, 'GetBTCprivateKey']);
    Route::get('bitcoin/wallet/details', [BitconWalletController::class, 'GetWalletDeatils']);


    //etherum
    Route::post('generate/etherum/wallet', [EtherumController::class, 'EthGenerateWallet']);
    Route::get('generate/etherum/address/{xpub}', [EtherumController::class, 'EthGenerateAddress']);
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
    Route::post('etherum/gas/fee', [EtherumController::class, 'EthEstimateGas']);
    Route::post('etherum/gas/fee/multiple', [EtherumController::class, 'EthEstimateGasMultiple']);
    Route::get('etherum/private/key/{user_id}', [EtherumController::class, 'GetETHprivateKey']);
    Route::get('etherum/wallet/details', [EtherumController::class, 'GetWalletDeatils']);

    //dogecoin
    Route::post('generate/dogecoin/wallet', [DogecoinController::class, 'DogeGenerateWallet']);
    Route::get('create/dogecoin/address/{xpub}', [DogecoinController::class, 'DogeGenerateAddress']);
    Route::post('dogecoin/create/privatekey', [DogecoinController::class, 'DogeGenerateAddressPrivateKey']);
    Route::get('dogecoin/blockchain/info', [DogecoinController::class, 'DogeGetBlockChainInfo']);
    Route::get('dogecoin/block/{i}', [DogecoinController::class, 'DogeGetBlockHash']);
    Route::get('dogecoin/block/hash/{hash}', [DogecoinController::class, 'DogeGetBlockByHash']);
    Route::get('dogecoin/transaction/{hash}', [DogecoinController::class, 'DogeGetRawTransaction']);
    Route::get('dogecoin/transaction/info/{hash}', [DogecoinController::class, 'DogeGetUTXO']);
    Route::post('dogecoin/transfer/{txHash}/{value}/{address}/{signatureId}/{receiveraddress}', [DogecoinController::class, 'DogeTransferBlockchain']);
    Route::post('dogecoin/broadcast', [DogecoinController::class, 'DogeBroadcast']);
    Route::post('dogecoin/gas/fee', [DogecoinController::class, 'DogeEstimateGas']);
    Route::get('dogecoin/wallet/details', [DogecoinController::class, 'GetWalletDeatils']);


    //Litecoin
    Route::post('generate/litecoin/wallet', [LitecoinController::class, 'LitecoinGenerateWallet']);
    Route::get('create/litecoin/address/{xpub}', [LitecoinController::class, 'LitecoinGenerateAddress']);
    Route::get('litecoin/blockchain/info', [LitecoinController::class, 'LtcGetBlockChainInfo']);
    Route::get('litecoin/block/{i}', [LitecoinController::class, 'LtcGetBlockHash']);
    Route::get('litecoin/block/hash/{hash}', [LitecoinController::class, 'LtcGetBlockyHash']);
    Route::get('litecoin/transaction/{hash}', [LitecoinController::class, 'LtcGetRawTransaction']);
    Route::get('litecoin/transaction/{address}', [LitecoinController::class, 'LtcGetTxByAddress']);
    Route::get('litecoin/transaction/info/{address}', [LitecoinController::class, 'LtcGetUTXO']);
    Route::post('litecoin/create/privatekey', [LitecoinController::class, 'LtcGenerateAddressPrivateKey']);
    Route::post('litecoin/transfer/{address}/{privateKey}/{receiveradd}/{value}', [LitecoinController::class, 'LtcTransferBlockchain']);
    Route::post('litecoin/broadcast', [LitecoinController::class, 'LtcBroadcast']);
    Route::post('litecoin/gas/fee', [LitecoinController::class, 'LtcEstimateGas']);
    Route::get('litecoin/wallet/details', [LitecoinController::class, 'GetWalletDeatils']);


    //polygon
    Route::post('generate/polygon/wallet', [PolygonController::class, 'PolygonGenerateWallet']);
    Route::get('create/polygon/address/{xpub}', [PolygonController::class, 'PolygonGenerateAddress']);
    Route::post('polygon/create/privatekey', [PolygonController::class, 'PolygonGenerateAddressPrivateKey']);
    Route::get('polygon/block/number', [PolygonController::class, 'PolygonGetCurrentBlock']);
    Route::get('polygon/block/{block}', [PolygonController::class, 'PolygonGetBlockbyHash']);
    Route::get('polygon/balance/{address}', [PolygonController::class, 'PolygonGetBalance']);
    Route::get('polygon/transaction/{address}', [PolygonController::class, 'PolygonGetTransactionByAddress']);
    Route::get('polygon/transactions/count/{address}', [PolygonController::class, 'PolygonGetTransactionCount']);
    Route::post('polygon/transfer', [PolygonController::class, 'PolygonBlockchainTransfer']);
    Route::post('polygon/invoke', [PolygonController::class, 'PolygonBlockchainSmartContractInvocation']);
    Route::post('polygon/broadcast', [PolygonController::class, 'PolygonBroadcast']);
    Route::post('polygon/gas/fee', [PolygonController::class, 'PolygonEstimateGas']);
    Route::get('polygon/private/key/{user_id}', [PolygonController::class, 'GetPolygonprivateKey']);
    Route::get('polygon/wallet/details', [PolygonController::class, 'GetWalletDeatils']);

    //binance
    Route::post('generate/binance/wallet', [BinanceController::class, 'BnbGenerateWallet']);
    Route::get('binance/block/number', [BinanceController::class, 'BnbGetCurrentBlock']);
    Route::get('binance/transaction/{height}', [BinanceController::class, 'BnbGetBlock']);
    Route::get('binance/balance/{address}', [BinanceController::class, 'BnbGetAccount']);
    Route::get('binance/transaction/{block}', [BinanceController::class, 'BnbGetTransaction']);
    Route::get('binance/account/transaction/{address}', [BinanceController::class, 'BnbGetTxByAccount']);
    Route::post('binance/transfer', [BinanceController::class, 'BnbBlockchainTransfer']);
    Route::post('binance/broadcast', [BinanceController::class, 'BnbBroadcast']);
    Route::get('binance/wallet/details', [BinanceController::class, 'GetWalletDeatils']);


    //virual account
    Route::post('virtual/create/customer/individual', [VirtualAccountController::class, 'createAccountIND']);
    Route::post('virtual/create/customer/company', [VirtualAccountController::class, 'createAccountCOMP']);
    Route::get('virtual/get/customer/{id}', [VirtualAccountController::class, 'GetCustomer']);
    Route::get('local/get/customer/{id}', [VirtualAccountController::class, 'GetCustomerLocal']);
    Route::post('update/virtual/account/individual/{id}/{user}', [VirtualAccountController::class, 'UpdateAccountIND']);
    Route::post('create/customer/card', [VirtualAccountController::class, 'createCard']);
    Route::get('get/customer/card/{id}', [VirtualAccountController::class, 'GetCustomerCard']);
    Route::get('get/customer/all/cards/{id}', [VirtualAccountController::class, 'GetCustomerCards']);
    Route::post('set/customer/card/pin/{id}', [VirtualAccountController::class, 'SetCardpin']);
    Route::post('change-card-pin/{id}', [VirtualAccountController::class, 'changeCardpin']);
    Route::post('updace-card-pin/{id}', [VirtualAccountController::class, 'UpdateCardpin']);


    //giftcard

   Route::post('giftcard/create/customer', [GiftcardController::class, 'CreateCustomer']);
   Route::get('giftcard/get/customer/details/{id}', [GiftcardController::class, 'CustomerDetails']);
   Route::post('giftcard/update/customer/{id}/{user_id}', [GiftcardController::class, 'UpdateCustomerDetails']);
   Route::post('create/gift/card/{location_id}', [GiftcardController::class, 'CreateGiftCard']);
   Route::post('link/customer/gift/card/{id}', [GiftcardController::class, 'LINKGiftCard']);
   Route::post('retrieve-gift-card-from-gan', [GiftcardController::class, 'RetrieveCardGAN']);
   Route::post('retrieve-gift-card/{id}', [GiftcardController::class, 'RetrieveGIFTCard']);
   Route::post('retrieve-gift-card-by-userid/{id}', [GiftcardController::class, 'RetrieveGIFTCardByUserID']);

  //activate , buy, sell
   Route::post('create-gift-card-activity', [GiftcardController::class, 'CreategiftCardActivity2']);
   Route::post('create-gift-card-activity-buy', [GiftcardController::class, 'CreategiftCardActivityBuy']);
   Route::post('create-gift-card-activity-sell', [GiftcardController::class, 'CreategiftCardActivitySell']);

   Route::post('giftcard/create/location', [GiftcardController::class, 'createLocation']);
   Route::get('giftcard/location/details/{id}', [GiftcardController::class, 'detailsLocation']);

   Route::get('giftcard/list-customers', [GiftcardController::class, 'listcustomer']);
   Route::post('giftcard/delete-customers/{id}', [GiftcardController::class, 'deletecustomer']);



    //airtime
    Route::post('get/airtime', [AirtimeController::class, 'GetAirtime']);
    Route::get('all/airtime', [AirtimeController::class, 'allAirtime']);

    //data
    Route::get('data/bundles', [DataController::class, 'DataBundles']);
    Route::post('get/data', [DataController::class, 'GetData']);


    //tv
    Route::get('tv/customer/details', [TVController::class, 'getCardInfo']);
    Route::post('get/tv/plan', [TVController::class, 'GetTVplan']);
    Route::get('get/tv/bundles', [TVController::class, 'TVBundles']);

    //power
    Route::get('meter-info', [PowerController::class, 'getMeterInfo']);
    Route::post('get/power/unit', [PowerController::class, 'BuyPower']);

   //utilitty
   Route::get('get/networks', [UtilityController::class, 'Networks']);
   Route::get('get-service/{id}', [UtilityController::class, 'getService']);

});



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
