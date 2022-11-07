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
use App\Http\Controllers\BlogPostController;
use App\Http\Controllers\BusinessController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\Savings\RotationalSavingController;
use App\Models\PosTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Savings\SavingController;
use App\Http\Controllers\Savings\GroupSavingController;
use App\Models\RotationalSaving;
use App\Http\Controllers\TransactionController;
use App\Models\AccountNumber;
use App\Models\User;
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



/*
| Default API Routes
|
*/

//Safe

Route::get('manage-activity',function (Request $request){

    $user = App\Models\User::where('id',$request->input('user_id'))->first();

//    return $peace->clientSavingsAccount($user,$kycs);
//    dispatch(new \App\Jobs\PeaceAccountCreationJob($request->user()));
    return 'Done and dusted';
});


Route::group(['middleware' => 'itexAuth'], function () {
    Route::post('terminal-notification-hook', [\App\Http\Controllers\POSController::class,'itexTerminalTransactionHook']);
});
Route::group(['prefix' => 'v1', 'middleware' => ['signature']], function(){

// Route::group(['middleware' => 'signature'], function() {
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
    Route::post('admin/login', [AdminController::class, 'login']);


// all routes that needs the cors middlewares added
    Route::middleware(['cors'])->group(function () {
        Route::post('create-account-reset', [UserController::class, 'retryAccountCreation']);
        Route::post('users/{user}', [UserController::class, 'update']);
//        Route::get('users', [UserController::class, 'index']);
        Route::get('users/{user}', [UserController::class, 'show']);
        Route::get('notifications', [UserController::class, 'Notification']);
        Route::get('admins', [UserController::class, 'Admin']);
        Route::get('states', 'Apis\UtilityController@getStates');
        Route::get('lga', 'Apis\UtilityController@getLga');
        Route::post('verify_bvn', [UserController::class, 'verifyBVN']);
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


        Route::get('faqs', [UserController::class, 'FAQ']);
        Route::post('contact-us', [UserController::class, 'ContactUs']);
        Route::post('logout', [UserController::class, 'logout']);
        Route::get('users/{user}', [UserController::class, 'show']);
        Route::post('imageupload', [UserController::class, 'ChangeImageNew']);

//        Route::post('userkyc-one', [UserController::class, 'kycUpdateOne']);
//        Route::post('userkyc-two', [UserController::class, 'kycUpdateTwo']);

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

            Route::post('agent/teller', [\App\Http\Controllers\CommissionController::class, 'logAgentTeller']);
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


        Route::group(['prefix'=>'admin', 'name'=>'admin.'],function (){
            Route::get('search-users/{key_word}', [AdminController::class, 'searchUsers']);
            Route::get('total-numbers', [AdminController::class,'totalNumbers']);
            Route::post('change/user-status', [AdminController::class, 'changeUserStatus']);
            Route::put('{change}', [AdminController::class, 'userActivation']);
            Route::post('change/user-auth', [AdminController::class, 'changeUserAuthorization']);
            Route::post('search-transactions', [AdminController::class, 'searchTransactions']);
            Route::post('export-transactions', [AdminController::class, 'exportTransactions']);
            Route::post('logout', [AdminController::class, 'logout']);
            Route::get('bills-transactions', [AdminController::class,'allBillPayments']);
            Route::get('all-savings', [AdminController::class,'allSavings']);
            Route::get('savings-info/{savings_id}', [AdminController::class,'savingsInfo']);
            Route::get('all-loans', [AdminController::class,'allLoans']);
            Route::get('all-business', [AdminController::class,'allBusiness']);
            Route::get('business-info/{business_id}', [AdminController::class,'businessInfo']);
            Route::get('all/card-requests', [AdminController::class,'allCardRequests']);
            Route::get('all-pos', [AdminController::class,'allPOS']);
            Route::get('user-info/{user_id}', [AdminController::class,'userInfo']);
            Route::get('users', [AdminController::class, 'index']);
            Route::get('wallet-transactions', [AdminController::class, 'allTransactions']);
//            Route::get('users/role/{role}', [AdminController::class, 'getUsersWithRole']);
            Route::get('staffs', [AdminController::class, 'fetchStaffs']);
            Route::post('create/staff', [AdminController::class, 'createStaff']);

            //Commissions
            Route::get('commission', [\App\Http\Controllers\CommissionController::class, 'index']);
            Route::post('commission', [\App\Http\Controllers\CommissionController::class, 'store']);


            //Accounting
            Route::get('accounting',[\App\Http\Controllers\CommissionController::class, 'transactionOnPlatform']);

            //Permissions
            Route::post('permissions', [PermissionController::class, 'grantRoleMultiplePermission']);
            Route::get('permission', [PermissionController::class, 'fetchPermissions']);
            Route::get('role', [PermissionController::class, 'fetchRoles']);
            Route::get('role-permissions', [PermissionController::class, 'fetchRolePermissions']);
//            Route::post('role', [PermissionController::class, 'createRole']);
            Route::put('role', [PermissionController::class, 'assignRolePermission']);
            Route::put('assign/user/role', [PermissionController::class, 'assignUserRole']);
//            Route::get('user/permission', [PermissionController::class, 'userPermission'])->middleware('role:level_1');

            //Blog endpoints
            Route::post('update-post/{slug}', [BlogPostController::class, 'update']);
            Route::post('create-post', [BlogPostController::class, 'create']);
            Route::get('delete-post/{slug}', [BlogPostController::class, 'destroy']);
        });

        Route::group(['prefix'=>'blog', 'name'=>'blog.',],function (){
            Route::get('posts', [BlogPostController::class, 'index']);
            Route::get('posts/{slug}', [BlogPostController::class, 'show']);
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

            //Group Savings
            Route::prefix('group')->name('group.')->group(function () {
                Route::post('create', [GroupSavingController::class, 'initSave']);
                Route::post('fund/card', [GroupSavingController::class, 'fundSavingsAccountFromCard']);
                Route::post('fund/wallet', [GroupSavingController::class, 'fundSavingsAccountFromWallet']);
                Route::post('fund/transfer', [GroupSavingController::class, 'fund_user_wallet_transfer']);
                Route::post('disburse/vote', [GroupSavingController::class, 'voteToDisburse']);
                Route::get('vote_results/{group_id}', [GroupSavingController::class, 'voteCount']);
                Route::post('join/request', [GroupSavingController::class, 'joinRequest']);
                Route::post('add/phonenumber', [GroupSavingController::class, 'addUserByPhoneNumber']);
                Route::post('request/accept', [GroupSavingController::class, 'addUserToGroup']);
                Route::get('requests/{groupId}', [GroupSavingController::class, 'allJoinRequests']);
                Route::get('{groupId}/members', [GroupSavingController::class, 'allGroupMembers']);
                Route::post('admin/create', [GroupSavingController::class, 'assignAdmin']);
                //Route::get('count', [GroupSavingController::class, 'countTransactions']);
                Route::post('disburse', [GroupSavingController::class, 'disburseSavings'])->middleware(['cors', 'bvn']);
                Route::get('admins/{groupId}', [GroupSavingController::class, 'allGroupAdmins']);
                Route::get('account/history/{group_id}', [GroupSavingController::class, 'getAccountHistory']);
                Route::get('member/history/{group_id}/{member_id}', [GroupSavingController::class, 'getMemberHistory']);
                Route::get('all', [GroupSavingController::class, 'allGroups']);
                Route::get('user_groups/{userId}', [GroupSavingController::class, 'usersGroups']);
            });

            //Rotational Savings
            Route::prefix('rotational')->name('rotational.')->group(function () {
                Route::post('create', [RotationalSavingController::class, 'initSave']);
                Route::post('fund/card', [RotationalSavingController::class, 'fundSavingsAccountFromCard']);
                Route::post('fund/wallet', [RotationalSavingController::class, 'fundSavingsAccountFromWallet']);
                Route::post('fund/transfer', [RotationalSavingController::class, 'fund_user_wallet_transfer']);
                Route::post('join/request', [RotationalSavingController::class, 'joinRequest']);
                Route::post('add/phonenumber', [RotationalSavingController::class, 'addUserByPhoneNumber']);
                Route::post('request/accept', [RotationalSavingController::class, 'addUserToGroup']);
                Route::get('requests/{groupId}', [RotationalSavingController::class, 'allJoinRequests']);
                Route::get('{groupId}/members', [RotationalSavingController::class, 'allGroupMembers']);
                Route::post('admin/create', [RotationalSavingController::class, 'assignAdmin']);
                Route::get('admins/{groupId}', [RotationalSavingController::class, 'allGroupAdmins']);
                Route::get('all', [RotationalSavingController::class, 'allGroups']);
                Route::post('account/history', [RotationalSavingController::class, 'getAccountHistory']);
                Route::post('member/history', [RotationalSavingController::class, 'getMemberHistory']);
                Route::post('position/assign', [RotationalSavingController::class, 'assignPosition']);
            });

            //Agent Savings
            Route::prefix('agent')->name('agent.')->group(function () {
                Route::post('create', [AgentSavingsController::class, 'initSave'])->middleware('cors');
                Route::post('user_fund/card', [AgentSavingsController::class, 'fundSavingsAccountFromCard']);
                Route::post('user_fund/wallet', [AgentSavingsController::class, 'fundSavingsAccountFromWallet']);
                Route::post('user_fund/transfer', [AgentSavingsController::class, 'fund_user_wallet_transfer']);
                Route::post('agent_fund/card', [AgentSavingsController::class, 'agentFundSavingsAccountFromCard']);
                Route::post('agent_fund/wallet', [AgentSavingsController::class, 'agentFundUserFromWallet']);
                Route::post('agent_fund/transfer', [AgentSavingsController::class, 'agent_fund_user_wallet_transfer']);
                Route::post('join/request', [AgentSavingsController::class, 'joinRequest']);
                Route::get('requests/{groupId}', [AgentSavingsController::class, 'allJoinRequests']);
                Route::get('{groupId}/members', [AgentSavingsController::class, 'allGroupMembers']);
                Route::post('member', [AgentSavingsController::class, 'getMember']);
                Route::get('all', [AgentSavingsController::class, 'allGroups']);
                Route::get('{groupId}/history', [AgentSavingsController::class, 'getAccountHistory']);
                Route::post('member/history', [AgentSavingsController::class, 'getMemberHistory']);
                Route::post('withdraw', [AgentSavingsController::class, 'withdrawFromFunds'])->middleware(['cors', 'bvn']);
                Route::post('break', [AgentSavingsController::class, 'closeAccount']);
                Route::post('set_penalty', [AgentSavingsController::class, 'setPenalty']);
                Route::post('request/accept', [AgentSavingsController::class, 'addUserToGroup']);
                Route::post('add_member', [AgentSavingsController::class, 'addUser']);
                Route::post('withdrawal_request', [AgentSavingsController::class, 'memberWithdrawalRequest']);
                Route::get('withdrawal_request/{groupId}', [AgentSavingsController::class, 'allWithdrawalRequests']);
                Route::post('withdrawal_request/accept', [AgentSavingsController::class, 'approveWithdrawalRequest']);
                Route::post('member_close_account', [AgentSavingsController::class, 'closeAccount']);
                Route::post('matured_savings', [AgentSavingsController::class, 'maturedSavings']);
                Route::post('withdraw_commission', [AgentSavingsController::class, 'withdrawCommission']);
                Route::get('{groupId}/commission_history', [AgentSavingsController::class, 'getCommAccountHistory']);
                Route::get('users_groups/{userId}', [AgentSavingsController::class, 'usersGroups']);
            });
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
