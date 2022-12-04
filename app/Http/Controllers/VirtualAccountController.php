<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Enums\ActivityType;
use App\Models\VirtualAccount;
use App\Traits\ManagesUsers;
use Illuminate\Support\Facades\Auth;
use App\Traits\ManagesResponse;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;

class VirtualAccountController extends Controller
{
    use  ManagesResponse, ManagesUsers;

    public function createAccount1(Request $request)
    {
        $user = Auth::user();

        $validator = Validator::make($request->all(), [
            'currency'=>'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $curl = curl_init();

        $payload = array(
        "currency" => $request->currency,
        "xpub" => $request->xpub
        );

        curl_setopt_array($curl, [
        CURLOPT_HTTPHEADER => [
            "Content-Type: application/json",
            "x-api-key: ". env('TATUM_TEST_KEY')
        ],
        CURLOPT_POSTFIELDS => json_encode($payload),
        CURLOPT_URL => "https://api.tatum.io/v3/ledger/account",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => "POST",
        ]);

        $response = curl_exec($curl);
        $error = curl_error($curl);

        curl_close($curl);

        if ($error) {
       return $error;
        } else {

            VirtualAccount::on('mysql::write')->create([
                'user_id' => auth()->user()->id,
                'virtual_id' => $request->id,
                // 'currency' => $request->currency,
                // 'active' => $request->active,
                // 'balance' => $request->balance,
                // 'accountBalance' =>  $request->accountBalance,
                // 'availableBalance' => $request->availableBalance,
                // 'frozen' => $request->frozen,
                // 'accountingCurrency' => $request->accountingCurrency,
                'response' => $response



            ], 201);


            $this->saveUserActivity(ActivityType::CREATE_VIRTUAL_ACCOUNT, '', $user->id);

            return response()->json([ 'status' => true, 'message' => 'Virtual account created successfully', 'response' => $response ], 201);
        }
    }


    public function createAccount(Request $request, $customer, $preferred_bank)
    {
        $url = "https://api.paystack.co/dedicated_account";

        $fields = [
          "customer" => $customer,
          "preferred_bank" => $preferred_bank
        ];

        $fields_string = http_build_query($fields);

        //open connection
        $ch = curl_init();

        //set the url, number of POST vars, POST data
        curl_setopt($ch,CURLOPT_URL, $url);
        curl_setopt($ch,CURLOPT_POST, true);
        curl_setopt($ch,CURLOPT_POSTFIELDS, $fields_string);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "Authorization: Bearer ". env('PAYSTACK_SECRET_KEY'),
          "Cache-Control: no-cache",
        ));

        //So that curl_exec returns the contents of the cURL; rather than echoing it
        curl_setopt($ch,CURLOPT_RETURNTRANSFER, true);

        //execute post
        $result = curl_exec($ch);

        if(curl_errno($ch)){
            return array('message'=>curl_error($ch), 'error'=>true);
        }

        $res = json_decode($result, true);

        curl_close($ch);
        return array('error'=>false, 'data'=>$res);
    }





}

