<?php

namespace App\Http\Controllers;

use App\Mail\TransactionMail;
use App\Models\AccountNumber;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Functions;
use App\Traits\ManagesUsers;
use Illuminate\Http\Request;
use App\Interfaces\UserInterface;
use App\Mail\DebitEmail;
use App\Mail\OtpMail;
use App\Models\Models\OtpVerify;
use App\Models\PaystackRefRecord;
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
use App\Http\Controllers\Apis\UtilityController;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;
use App\Traits\SendSms;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class TransactionController extends Controller
{
    use  ManagesUsers;


    /**
     * UserController constructor.
     * @param UserInterface $user
     */
    public $utility;

    public function __construct(UtilityController $utility)
    {
        $this->utility = $utility;
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

            $validator = Validator::make($request->all(), [
                'account_number'=>'required',
                'bank_code' => 'required'
            ]);

            if ($validator->fails()) {
                return response()->json($validator->errors(), 422);
            }

            $account_number = $request->account_number;
            $bank_code = $request->bank_code;


            $response = Http::withHeaders([
                'Authorization' => 'Bearer '.env('PAYSTACK_SECRET_KEY')
            ])->get(env('PAYSTACK_BASE_URL')."/bank/resolve?account_number=$account_number&bank_code=$bank_code");

            return response()->json(['account'=>$response['data']]);

        }catch(Exception $e){
            return response()->json(['message'=>$e->getMessage()], 422);
        }

    }

    public function TransferRecipient(Request $request) {

        $data = array(
            'account_number'      =>  $request->account_number,
            'bank_code'     =>  $request->bank_code,
        );
        $validator = Validator::make($request->all(), [
            'account_number'=>'required',
            'bank_code' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        if($res = $this->createRecipient($data)) {
            // dd($res);
            return $res;
        }

    }

    public function createRecipient(array $data)
    {
        $response = Http::asForm()->withHeaders([
            'Authorization' => 'Bearer '.env('PAYSTACK_SECRET_KEY'),
            'Content-Type' =>  'application/json'
        ])->post(env('PAYSTACK_BASE_URL') . '/transferrecipient', $data);
        $response = $response->getBody()->getContents();

        return $response;

    }


}
